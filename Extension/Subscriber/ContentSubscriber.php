<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Extension\Subscriber;

use Doctrine\Common\Persistence\ObjectManager;

use Integrated\Bundle\UserBundle\Model\UserInterface;

use Integrated\Bundle\WorkflowBundle\Entity\Workflow\Log;
use Integrated\Bundle\WorkflowBundle\Entity\Workflow\State;

use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\Extension\Event\ContentEvent;
use Integrated\Common\Content\Extension\Event\Subscriber\ContentSubscriberInterface;
use Integrated\Common\Content\Extension\Events;
use Integrated\Common\Content\Extension\ExtensionInterface;
use Integrated\Common\Content\MetadataInterface;

use Integrated\Common\ContentType\Resolver\ContentTypeResolverInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Security\Core\Util\ClassUtils;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ContentSubscriber implements ContentSubscriberInterface
{
	const CONTENT_CLASS = 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\Relation\\Relation';

	/**
	 * @var ExtensionInterface
	 */
	private $extension;

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var ObjectManager
	 */
	private $manager = null;

	/**
	 * @var ContentTypeResolverInterface
	 */
	private $resolver = null;

	/**
	 * @param ExtensionInterface $extension
	 * @param ContainerInterface $container
	 */
	public function __construct(ExtensionInterface $extension, ContainerInterface $container)
	{
		$this->extension = $extension;
		$this->container = $container;
	}

	/**
	 * @inheritdoc
	 */
	public static function getSubscribedEvents()
	{
		return [
			Events::POST_READ   => 'read',
			Events::POST_CREATE => 'update',
			Events::PRE_UPDATE  => 'update',
			Events::POST_DELETE => 'delete'
		];
	}

	public function read(ContentEvent $event)
	{
		$content = $event->getContent();

		if (!$this->isSupported($content)) {
			return;
		}

		// check if there is a workflow state for this item else just set
		// everything to empty.

		$data = [
			'comment'  => '',
			'state'    => null,
			'assigned' => null,
			'deadline' => null,
		];

		if ($state = $this->getState($content)) {
			$data = [
				'comment'  => '',
				'state'    => $state->getState(),
				'assigned' => $state->getAssignedType() == 'user' ? $state->getAssigned() : null,
				'deadline' => $state->getDeadline(),
			];
		}

		$event->setData($data);
	}

	public function update(ContentEvent $event)
	{
		$content = $event->getContent();

		if (!$this->isSupported($content)) {
			return;
		}

		$data = $event->getData();

		if (!$state = $this->getState($content)) {
			$state = new State();
			$state->setContent($content);

			$this->getManager()->persist($state);
		}

		$persist = false;

		$log = new Log();
		$log->setUser($this->getUser());

		if ($data['comment']) {
			$log->setComment($data['comment']);

			$persist = true;
		}

		// log the old settings if changed

		if ($data['state'] !== $state->getState()) {
			$log->setState($data['state']);
			$state->setState($data['state']);

			$persist = true;
		}

		if ($data['assigned'] !== $state->getAssigned()) {
			$state->setAssigned($data['assigned']);
		}

		if ($data['deadline'] !== $state->getDeadline()) {
			$log->setDeadline($data['deadline']);
			$state->setDeadline($data['deadline']);

			$persist = true;
		}

		if ($persist) {
			$this->getManager()->persist($log);

			$state->addLog($log);
		}

		$this->getManager()->flush($state);

		// also add the current workflow state to the content metadata

		if ($content instanceof MetadataInterface) {
			$content->getMetadata()->set('workflow', $state->getState()->getWorkflow()->getId());
			$content->getMetadata()->set('workflow_state', $state->getState()->getId());
		}
	}

	public function delete(ContentEvent $event)
	{
		$content = $event->getContent();

		if (!$this->isSupported($content)) {
			return;
		}

		if ($state = $this->getState($content)) {
			$this->getManager()->remove($state);
		}

		$event->setData(null);

		if ($content instanceof MetadataInterface) {
			$content->getMetadata()->remove('workflow');
			$content->getMetadata()->remove('workflow.state');
		}
	}

	/**
	 * @param ContentInterface $content
	 * @return State | null
	 */
	protected function getState(ContentInterface $content)
	{
		$repository = $this->getManager()->getRepository('Integrated\\Bundle\\WorkflowBundle\\Entity\\Workflow\\State');

		if ($entity = $repository->findOneBy(['content' => $content])) {
			return $entity;
		}

		return null;
	}

	/**
	 * @return UserInterface | null
	 */
	protected function getUser()
	{
		if (!$this->container->has('security.context')) {
			return null;
		}

		if (null === $token = $this->container->get('security.context')->getToken()) {
			return null;
		}

		$user = $token->getUser();

		if (!$user instanceof UserInterface) {
			return null;
		}

		return $user;
	}

	protected function isSupported($object)
	{
		if (!$object instanceof ContentInterface) {
			return false;
		}

		// does this content even have a workflow connected ?

		if ($type = $this->getResolver()->getType(ClassUtils::getRealClass($object), $object->getContentType())) {
			if ($type->getOption('workflow')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return ExtensionInterface
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * @return ContainerInterface
	 */
	protected function getContainer()
	{
		return $this->container;
	}

	/**
	 * @return ObjectManager
	 */
	protected function getManager()
	{
		if ($this->manager === null) {
			$this->manager = $this->getContainer()->get('integrated_workflow.extension.doctrine.object_manager');
		}

		return $this->manager;
	}

	/**
	 * @return ContentTypeResolverInterface
	 */
	protected function getResolver()
	{
		if ($this->resolver === null) {
			$this->resolver = $this->getContainer()->get('integrated.form.resolver'); // hax
		}

		return $this->resolver;
	}
}