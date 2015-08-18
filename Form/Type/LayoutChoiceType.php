<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

use Integrated\Bundle\PageBundle\Locator\LayoutLocator;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class LayoutChoiceType extends AbstractType
{
    /**
     * @var LayoutLocator
     */
    private $locator;

    /**
     * @param LayoutLocator $locator
     */
    public function __construct(LayoutLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'theme'   => 'default',
            'choices' => function(Options $options) { return $this->getChoices($options['theme']); },
        ]);
    }

    /**
     * @param string $theme
     * @return array
     */
    protected function getChoices($theme)
    {
        $layout = $this->locator->getLayouts($theme);

        return array_combine($layout, $layout);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'integrated_page_layout_choice';
    }
}
