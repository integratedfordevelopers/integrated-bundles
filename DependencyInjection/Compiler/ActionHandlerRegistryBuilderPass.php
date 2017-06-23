<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class ActionHandlerRegistryBuilderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('integrated_content.bulk.registry.action_handler_registry')) {
            return;
        }

        $builder = $container->getDefinition('integrated_content.bulk.registry.action_handler_registry_builder');

        foreach ($container->findTaggedServiceIds('integrated_content.bulk.action.handler') as $service => $tags) {
            $registry->addMethodCall('addHandler', [$container->getDefinition($service)]);
        }
    }
}
