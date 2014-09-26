<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Form\EventListener;

use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Entity\Definition\State;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ExtractTransitionsFromDataListener implements EventSubscriberInterface
{
    /**
   	 * {@inheritdoc}
   	 */
	public static function getSubscribedEvents()
	{
		return [
			FormEvents::PRE_SET_DATA => 'onPreSetData'
		];
	}

	/**
	 * Add the transitions field to the form type
	 *
	 * @param FormEvent $event
	 */
	public function onPreSetData(FormEvent $event)
	{
		$form = $event->getForm();

		if ($form->has('transitions')) {
			$form->remove('transitions');
		}

		$form->add('transitions', 'choice', [
			'required'    => false,

			'choice_list' => $this->getChoiceList($event->getData()),

			'multiple'    => true,
			'expanded'    => false,
		]);
	}

	/**
	 * Build a choice list based on the given data.
	 *
	 * The states in the choice list are just extracted from the workflow the state is in. If
	 * form some reason the data is not a State of it does not have a workflow then a empty
	 * choice list is returned
	 *
	 * @param mixed $data
	 *
	 * @return ChoiceList
	 */
	protected function getChoiceList($data)
	{
		if ($data instanceof State && $data->getWorkflow()) {
			$choices = [];

			foreach($data->getWorkflow()->getStates() as $state) {
				if ($data !== $state) {	$choices[] = $state; }
			}

			return new ObjectChoiceList($choices, 'name', [], null, 'id');
		}

		return new SimpleChoiceList([]);
	}
}