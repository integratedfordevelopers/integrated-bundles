<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Command\Filesystem;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\Common\Util\ClassUtils;
use Integrated\Bundle\StorageBundle\Storage\Reader\MemoryReader;
use Integrated\Bundle\StorageBundle\Storage\Reflection\Document\DoctrineDocument;
use Integrated\Bundle\StorageBundle\Storage\Reflection\PropertyReflection;
use Integrated\Bundle\StorageBundle\Storage\Validation\FilesystemValidation;
use Integrated\Bundle\StorageBundle\Storage\Registry\FilesystemRegistry;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Storage\Database\DatabaseInterface;
use Integrated\Common\Storage\ManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Redistribute files in the database over the filesystems.
 *
 * @author Johnny Borg <johnny@e-active.nl>
 */
class RedistributeCommand extends Command
{
    /**
     * @var DatabaseInterface
     */
    protected $database;

    /**
     * @var FilesystemRegistry
     */
    protected $registry;

    /**
     * @var ManagerInterface
     */
    protected $storage;

    /**
     * @var PropertyReflection[]
     */
    protected $reflectionClasses;

    /**
     * @param DatabaseInterface $database
     * @param FilesystemRegistry $registry
     * @param ManagerInterface $storage
     */
    public function __construct(DatabaseInterface $database, FilesystemRegistry $registry, ManagerInterface $storage)
    {
        $this->database = $database;
        $this->registry = $registry;
        $this->storage = $storage;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('storage:filesystem:redistribute')
            ->setDescription('Redistribute files in the database over the filesystems.')
            ->setDefinition([
                new InputArgument(
                    'filesystems',
                    InputArgument::IS_ARRAY,
                    'A space separated list of storage keys'
                ),
                new InputOption(
                    'delete',
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Delete the file before putting it in storage(s)'
                )
            ]);
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting redistribution of the filesystem');

        // Get the filesystems (all if none specified )
        $filesystems = (new FilesystemValidation($this->registry))
            ->getValidFilesystems(new ArrayCollection($input->getArgument('filesystems')));

        // Fetch all data from database
        $data = $this->database->getObjects();

        // Barry progress
        $progress = new ProgressBar($output, $data->count());
        $progress->start();
        $progress->setFormat('%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progress->setRedrawFrequency(75);

        foreach ($data as $row) {
            // This will be used to determine if we need to update the object
            $document = new DoctrineDocument($row);

            // Loop trough the documents properties that might contain a file
            foreach ($this->getReflectionClass(ClassUtils::getClass($row))->getTargetProperties() as $property) {
                /** @var StorageInterface|bool $file */
                if ($file = $document->get($property->getPropertyName())) {
                    // Update the document
                    $document->set(
                        $property->getPropertyName(),
                        // Let the manager decide which filesystems will be affected
                        $this->storage->write(
                            new MemoryReader($this->storage->read($file), $file->getMetadata()),
                            $filesystems
                        )
                    );
                }
            }

            // Update the field in the database
            if ($document->hasUpdates()) {
                $this->database->saveObject($row);
            }

            // Notify barry about our progress
            $progress->advance();
        }

        // Let barry and the user go
        $progress->finish();
        $output->writeln('Done!');
    }

    /**
     * @param string $class
     * @return PropertyReflection
     */
    protected function getReflectionClass($class)
    {
        if (isset($this->reflectionClasses[$class])) {
            return $this->reflectionClasses[$class];
        }

        return $this->reflectionClasses[$class] = new PropertyReflection($class);
    }
}
