<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Storage\Reflection;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
interface ReflectionCacheInterface
{
    /**
     * @param string $class
     * @return PropertyReflection
     */
    public function getPropertyReflectionClass($class);
}
