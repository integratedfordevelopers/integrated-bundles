<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Form\Type;

use Integrated\Bundle\UserBundle\Form\DataMapper\UserMapper;
use Integrated\Bundle\UserBundle\Form\EventListener\UserProfileExtensionListener;
use Integrated\Bundle\UserBundle\Form\EventListener\UserProfileOptionalListener;
use Integrated\Bundle\UserBundle\Form\EventListener\UserProfilePasswordListener;

use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Bundle\UserBundle\Validator\Constraints\UniqueUser;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Util\SecureRandomInterface;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

use ReflectionClass;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ProfileFormType extends AbstractType
{
	/**
	 * @var UserManagerInterface
	 */
	private $manager;

	/**
	 * @var SecureRandomInterface
	 */
	private $generator;

	/**
	 * @var EncoderFactoryInterface
	 */
	private $encoderFactory;

	/**
	 * @param UserManagerInterface $manager
	 * @param SecureRandomInterface $generator
	 * @param EncoderFactoryInterface $encoder
	 */
	public function __construct(UserManagerInterface $manager, SecureRandomInterface $generator, EncoderFactoryInterface $encoder)
	{
		$this->manager = $manager;

		$this->generator = $generator;
		$this->encoderFactory = $encoder;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if ($options['optional']) {
			$builder->add('enabled', 'checkbox', [
				'label' => 'Enable login',
				'mapped' => false
			]);

			// this has to be a event listener as data will not be mapped to this
			// field, even if mapped is set to true, when the field value is false.

			$builder->addEventSubscriber(new UserProfileOptionalListener());
		}

		$builder->add('username', 'text', [
			'constraints' => [
				new NotBlank(),
				new Length(['min' => 3])
			]
		]);

		$builder->add('password', 'password', [
			'mapped' => false,
			'constraints' => [
				new NotBlank(),
				new Length(['min' => 6])
			]
		]);

		if (!$options['optional']) {
			$builder->add('enabled', 'checkbox', [
				'label' => 'Enable login'
			]);
		}

		$builder->add('groups', 'user_group_choice');

		$builder->addEventSubscriber(new UserProfilePasswordListener($this->generator, $this->encoderFactory));
		$builder->addEventSubscriber(new UserProfileExtensionListener('integrated.extension.user'));

		if ($options['optional']) {
			$builder->setDataMapper(new UserMapper());
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'optional'    => false, // everything can be left empty if enabled is not checked

			'data_class'  => $this->getManager()->getClassName(),
			'empty_data'  => function(FormInterface $form) {
				if ($form->has('enabled') && $form->get('enabled')->getData() == false)	{
					return null;
				}

				return $this->getManager()->create();
			},

			'constraints' => new UniqueUser($this->manager),
		));

		$resolver->setAllowedTypes([
			'optional'    => 'bool'
		]);

		$resolver->setDefaults([
			'validation_groups' => function (Options $options, $previous) {
				if (!$options['optional']) {
					return $previous; // does not need all the processing
				}

				// validation should be disabled when enabled is not checked

				return function (FormInterface $form) use ($previous) {
					$resolve = function (FormInterface $form) use ($previous) {
						if ($form->has('enabled') && $form->get('enabled')->getData() == false) {
							return false;
						}

						return $previous;
					};

					if (null !== ($groups = $resolve($form))) {
						return $groups;
					}

					// yeah now we are going to cheat as we don't want to rewrite what is already
					// made by someone else.

 					$reflection = new ReflectionClass('Symfony\Component\Form\Extension\Validator\Constraints\FormValidator');

					$method = $reflection->getMethod('getValidationGroups');
					$method->setAccessible(true);

					return $method->invoke(null, $form->getParent());
				};
			}
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'integrated_user_profile_form';
	}

	/**
	 * @return UserManagerInterface
	 */
	public function getManager()
	{
		return $this->manager;
	}
}