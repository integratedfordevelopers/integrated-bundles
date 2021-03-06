<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SlugBundle\Mapping\Metadata;

use Metadata\PropertyMetadata as BasePropertyMetadata;

/**
 * Property metadata with slug properties
 *
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class PropertyMetadata extends BasePropertyMetadata
{
    /**
     * @var array
     */
    public $slugFields = [];

    /**
     * @var string
     */
    public $slugSeparator = '-';

    /**
     * @var int
     */
    public $slugLengthLimit = 200;
}
