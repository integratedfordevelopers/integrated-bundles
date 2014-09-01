<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\EventListener;

use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\Content\Form\Events;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ContentWorkflowIntegrationListener implements EventSubscriberInterface
{
	/**
	 * @inheritdoc
	 */
	public static function getSubscribedEvents()
	{
		return [
			Events::POST_BUILD => ['buildForm', 99]
		];
	}

	public function buildForm(BuilderEvent $event)
	{
		if ($event->getMetadata()->hasOption('workflow') && $event->getContentType()->hasOption('workflow')) {
			$event->getBuilder()->add('extension_workflow', 'workflow', [
				'property_path' => 'extensions[integrated.extension.workflow]',
				'workflow' => $event->getContentType()->getOption('workflow')
			]);
		}
	}
}