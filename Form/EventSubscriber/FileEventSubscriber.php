<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Form\EventSubscriber;

use Integrated\Bundle\StorageBundle\Form\Upload\StorageIntentUpload;
use Integrated\Bundle\StorageBundle\Storage\Reader\UploadedFileReader;

use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Storage\DecisionInterface;
use Integrated\Common\Storage\ManagerInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class FileEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @param ManagerInterface $manager
     */
    public function __construct(ManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'submit'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        // This (if any) is the new file
        $upload = $event->getForm()->get('file')->getData();

        // This is (if any) the old file
        $original = $event->getData();

        // Delete comes first then a upload and lastly the original (if it meets our interface)
        if ($event->getForm()->get('remove')->getData()) {
            // Delete the set the property to null
            $event->setData(null);
        } elseif ($upload instanceof UploadedFile) {
            // Make sure the entity ends up a StorageInterface
            $event->setData(
                 new StorageIntentUpload($event->getForm()->getData(), $upload)
            );
        } elseif ($original instanceof StorageInterface) {
            // Set the something we don't know
            $event->setData($original);
        } else {
            // Last resort
            $event->setData($upload);
        }
    }
}
