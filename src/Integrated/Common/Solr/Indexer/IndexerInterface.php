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

use Integrated\Common\Solr\Exception\InvalidArgumentException;

use Solarium\Core\Client\Client;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
interface IndexerInterface extends SerializerAwareInterface
{
	/**
	 * Set the queue.
	 *
	 * @param QueueInterface $queue
	 */
	public function setQueue(QueueInterface $queue);

	/**
	 * Set the solarium client.
	 *
	 * @param Client $client
	 */
	public function setClient(Client $client);

	/**
	 * Set the serializer.
	 *
	 * @param SerializerInterface $serializer
	 */
	public function setSerializer(SerializerInterface $serializer);

	/**
	 * This wil execute a indexing run by getting jobs from the queue.
	 *
	 * @param Client $client if supplied this will overwrite the current set client
	 *
	 * @return mixed
	 *
	 * @throws InvalidArgumentException if no solarium client is supplied
	 * @throws Exception a error not generated by the indexer
	 */
	public function execute(Client $client = null);
}