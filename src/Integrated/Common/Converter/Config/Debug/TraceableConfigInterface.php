<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Converter\Config\Debug;

use Integrated\Common\Converter\Config\ConfigInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
interface TraceableConfigInterface extends ConfigInterface
{
    /**
     * @return string
     */
    public function getClass();
}
