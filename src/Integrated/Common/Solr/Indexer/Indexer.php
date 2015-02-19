<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Indexer;

use Exception;

use Integrated\Common\Converter\ConverterInterface;

use Integrated\Common\Queue\Queue;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Queue\QueueMessageInterface;
use Integrated\Common\Queue\Provider\Memory\QueueProvider;

use Integrated\Common\Solr\Indexer\Event\BatchEvent;
use Integrated\Common\Solr\Indexer\Event\ErrorEvent;
use Integrated\Common\Solr\Indexer\Event\IndexerEvent;
use Integrated\Common\Solr\Indexer\Event\MessageEvent;
use Integrated\Common\Solr\Indexer\Event\ResultEvent;
use Integrated\Common\Solr\Indexer\Event\SendEvent;

use Integrated\Common\Solr\Exception\ClientException;
use Integrated\Common\Solr\Exception\ExceptionInterface;
use Integrated\Common\Solr\Exception\InvalidArgumentException;
use Integrated\Common\Solr\Exception\OutOfBoundsException;
use Integrated\Common\Solr\Exception\SerializerException;

use Solarium\Core\Client\Client;

use Solarium\QueryType\Update\Query\Command\Add;
use Solarium\QueryType\Update\Query\Command\Command;
use Solarium\QueryType\Update\Query\Command\Commit;
use Solarium\QueryType\Update\Query\Command\Delete;
use Solarium\QueryType\Update\Query\Command\Optimize;
use Solarium\QueryType\Update\Query\Command\Rollback;

use Solarium\QueryType\Update\Query\Document\Document;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class Indexer implements IndexerInterface
{
	/**
	 * @var EventDispatcherInterface
	 */
	private $dispatcher;

	/**
	 * @var QueueInterface
	 */
	private $queue;

	/**
	 * @var SerializerInterface
	 */
	private $serializer;

	/**
	 * @var ConverterInterface
	 */
	protected $converter;

	/**
	 * @var Client
	 */
	private $client = null;

	/**
	 * @var Batch
	 */
	private $batch = null;

	/**
	 * @var OptionsResolver
	 */
	private $resolver = null;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{
		$this->setOptions($options);
	}

	/**
	 * Replace the options with a new set op options
	 *
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		$this->options = $this->getResolver()->resolve($options);
	}

	/**
	 * Get all the options
	 *
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Set the value for the given key
	 *
	 * @param $key
	 * @param $value
	 */
	public function setOption($key, $value)
	{
		$options = $this->getOptions();
		$options[$key] = $value;

		$this->setOptions($options);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function hasOption($key)
	{
		return isset($this->options[$key]);
	}

	/**
	 * Get the option or return the default if none is set
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getOption($key)
	{
		return $this->options[$key];
	}

	/**
	 * Get the option resolver
	 *
	 * @return OptionsResolver
	 */
	protected function getResolver()
	{
		if ($this->resolver === null) {
			$this->resolver = new OptionsResolver();

			// config the resolver

			$this->resolver->setDefaults([
				'queue.size' => 1000,
				'batch.size' => 10,
			]);

			$this->resolver->setAllowedTypes([
				'queue.size' => 'integer',
				'batch.size' => 'integer',
			]);
		}

		return $this->resolver;
	}

	/**
	 * Set the event dispatcher
	 *
	 * @param EventDispatcherInterface $dispatcher
	 */
	public function setEventDispatcher(EventDispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Get the event dispatcher.
	 *
	 * If no event dispatcher is set then a default EventDispatcher is created.
	 *
	 * @return EventDispatcherInterface
	 */
	public function getEventDispatcher()
	{
		if ($this->dispatcher === null) {
			$this->dispatcher = new EventDispatcher();
		}

		return $this->dispatcher;
	}

	/**
	 * @inheritdoc
	 */
	public function setQueue(QueueInterface $queue)
	{
		$this->queue = $queue;
	}

	/**
	 * Get the queue.
	 *
	 * If no queue is set then a empty Queue is created.
	 *
	 * @return QueueInterface
	 */
	public function getQueue()
	{
		if ($this->queue === null) {
			$this->queue = new Queue(new QueueProvider(), 'indexer');
		}

		return $this->queue;
	}

	/**
	 * @inheritdoc
	 */
	public function setSerializer(SerializerInterface $serializer)
	{
		$this->serializer = $serializer;
	}

	/**
	 * Get the serializer.
	 *
	 * If no queue is set then a default Serializer is created.
	 *
	 * @return SerializerInterface
	 */
	public function getSerializer()
	{
		if ($this->serializer === null) {
			$this->serializer = new Serializer();
		}

		return $this->serializer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setConverter(ConverterInterface $converter)
	{
		$this->converter = $converter;
	}

	/**
	 * @return ConverterInterface
	 */
	public function getConverter()
	{
		return $this->converter;
	}

	/**
	 * @inheritdoc
	 */
	public function setClient(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Get the solarium client
	 *
	 * @return Client|null
	 */
	public function getClient()
	{
		return $this->client;
	}

	/**
	 * Get the batch
	 *
	 * If no batch is set then one will be created.
	 *
	 * @return Batch
	 */
	protected function getBatch()
	{
		if ($this->batch === null) {
			$this->batch = new Batch();
		}

		return $this->batch;
	}

	/**
	 * @inheritdoc
	 */
	public function execute(Client $client = null)
	{
		if ($client !== null) {
			$this->setClient($client);
		}

		if ($this->getClient() === null) {
			throw new InvalidArgumentException(sprintf('No instance of a Solarium\Core\Client\Client has been inserted into the indexer.'));
		}

		try {
			$this->getEventDispatcher()->dispatch(Events::PRE_EXECUTE, new IndexerEvent($this));

			try {
				foreach ($this->getQueue()->pull($this->getOption('queue.size')) as $message) {
					$this->batch($message);
				}

				$this->send(); // send the last batch if there is any
			} catch (ExceptionInterface $e) {
				$this->getEventDispatcher()->dispatch(Events::ERROR, new ErrorEvent($this, $e));

				throw $e;
			}

			$this->clean();

			$this->getEventDispatcher()->dispatch(Events::POST_EXECUTE, new IndexerEvent($this));
		} catch (Exception $e) {
			$this->clean(); // clean up before exiting

			throw $e;
		}
	}

	/**
	 * A queue message it not send to the solr server but grouped in a
	 * batch to send more operations at ones.
	 *
	 * @param QueueMessageInterface $message
	 */
	protected function batch(QueueMessageInterface $message)
	{
		$operation = new BatchOperation($message, $this->convert($message->getPayload()));

		// Send a event to allow the batch operation to be changed by
		// external code. After that check if the batch is cancels or
		// not. If canceled remove the message from the queue.

		$this->getEventDispatcher()->dispatch(Events::BATCHING, new BatchEvent($this, $operation));

		if ($operation->getCommand() === null) {
			$this->delete($message);
			return;
		}

		// make a clone so the batch operation it self can not be modified
		// by external code anymore.

		$batch = $this->getBatch();
		$batch->add(clone $operation);

		if ($batch->count() >= $this->getOption('batch.size')) {
			$this->send();
		}
	}

	/**
	 * Convert a job into a solarium update command.
	 *
	 * @param JobInterface $job
	 *
	 * @return Command|null
	 *
	 * @throws OutOfBoundsException if the message type is empty or invalid
	 * @throws SerializerException if there was problem deserializing a document
	 */
	protected function convert(JobInterface $job)
	{
		if (!$job->hasAction()) {
			throw new OutOfBoundsException(sprintf('The jobs action is empty, valid actions are "%s"', 'ADD, DELETE, OPTIMIZE, ROLLBACK or COMMIT'));
		}

		$command = null;

		// Action specifies which command class to use where the options got
		// optional argument. Except for the ADD and DELETE command. The ADD
		// command need to deserialize a document and for that need the options
		// "document.data", "document.class" and "document.format". And the
		// DELETE command needs a "id" or a "query" but both are also allowed.
		// if those requirements are not met then no command will be created
		// and the result will be null.
		//
		// Options that are not used are ignored and will not generated any
		// kind of error.

		switch (strtoupper($job->getAction())) {
			case 'ADD':

				$document = null;

				if ($job->hasOption('document.data') && $job->hasOption('document.class') && $job->hasOption('document.format')) {
					try {
						$document = $this->getSerializer()->deserialize($job->getOption('document.data'), $job->getOption('document.class'), $job->getOption('document.format'));
					} catch (Exception $e) {
						throw new SerializerException($e->getMessage(), $e->getCode(), $e);
					}

                    if ($document !== null && ($document = $this->getConverter()->convert($document)) && $document->count()) {
                        $command = new Add();
                        $command->addDocument(new Document($document->toArray()));

                        if ($job->hasOption('overwrite')) {
                            $command->setOverwrite((bool) $job->getOption('overwrite'));
                        }
                        if ($job->hasOption('commitwithin')) {
                            $command->setCommitWithin((bool) $job->getOption('commitwithin'));
                        }
                    }
                }

				if ($command === null) {
					// check if there is a id so that the document can be removed from
					// the index instead.

					if ($job->hasOption('document.id')) {
						$command = new Delete();
						$command->addId($job->getOption('document.id'));
					}
				}

				break;

			case 'DELETE':

				if ($job->hasOption('id') || $job->hasOption('query')) {
					$command = new Delete();

					if ($job->hasOption('id')) {
						$command->addId($job->getOption('id'));
					}

					if ($job->hasOption('query')) {
						$command->addQuery($job->getOption('query'));
					}
				}

				break;

			case 'OPTIMIZE':
				$command = new Optimize();

				if ($job->hasOption('maxsegments')) {
					$command->setMaxSegments((bool) $job->getOption('maxsegments'));
				}

				if ($job->hasOption('waitsearcher')) {
					$command->setWaitSearcher((bool) $job->getOption('waitsearcher'));
				}

				if ($job->hasOption('softcommit')) {
					$command->setSoftCommit((bool) $job->getOption('softcommit'));
				}

				break;

			case 'COMMIT':
				$command = new Commit();

				if ($job->hasOption('waitsearcher')) {
					$command->setWaitSearcher((bool) $job->getOption('waitsearcher'));
				}

				if ($job->hasOption('softcommit')) {
					$command->setSoftCommit((bool) $job->getOption('softcommit'));
				}

				if ($job->hasOption('expungedeletes')) {
					$command->setExpungeDeletes((bool) $job->getOption('expungedeletes'));
				}

				break;

			case 'ROLLBACK':
				$command = new Rollback();
				break;

			default:
				throw new OutOfBoundsException(sprintf('The jobs action "%s" does not exist, valid actions are "%s"', $job->getAction(), 'ADD, DELETE, OPTIMIZE, ROLLBACK or COMMIT'));
		}

		return $command;
	}

	/**
	 * Send all the operation in the batch to the solr server.
	 *
	 * @throws ClientException when something goes wrong when transmitting the request
	 */
	protected function send()
	{
		$batch = $this->getBatch();

		if ($batch->count() == 0) {
			return; // empty batch
		}

		// Build the query to send to the solr server

		$solr  = $this->getClient();
		$query = $solr->createUpdate();

		foreach ($batch->getIterator() as $operation) {
			$query->add(null, $operation->getCommand());
		}

		$this->getEventDispatcher()->dispatch(Events::SENDING, new SendEvent($this, $query));

		try	{
			$result = $solr->execute($query);
		} catch (Exception $e)	{
			throw new ClientException($e->getMessage(), $e->getCode(), $e);
		}

		$this->getEventDispatcher()->dispatch(Events::RESULTS, new ResultEvent($this, $result));

		// The query is executed so now the messages can be remove
		// from the queue.

		foreach ($batch->getIterator() as $operation) {
			$this->delete($operation->getMessage());
		}

		$batch->clear();
	}

	/**
	 * Delete the queue message from the queue and send a event update
	 * that the message has been processes.
	 *
	 * @param QueueMessageInterface $message
	 */
	protected function delete(QueueMessageInterface $message)
	{
		$message->delete();
		$this->getEventDispatcher()->dispatch(Events::PROCESSED, new MessageEvent($this, $message));
	}

	/**
	 * Clean up the indexer.
	 */
	protected function clean()
	{
		if ($this->batch) {
			$this->batch->clear();
		}

		$this->batch = null;
	}
}