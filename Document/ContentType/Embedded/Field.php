<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\ContentType\Embedded;

use Integrated\Common\ContentType\ContentTypeFieldInterface;

/**
 * Embedded document Field
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class Field implements ContentTypeFieldInterface
{
    /**
     * @var string The name of the property of the content type
     */
    protected $name;

    /**
     * @var string The type of the form field
     */
    protected $type;

    /**
     * @var array The options of the form field
     */
    protected $options = array();

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the field
     *
     * @param string $name The name of the property of the content type
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of the field
     *
     * @param string $type The type of the form field
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the options of the field
     *
     * @param array $options The options of the form field
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Added shortcut to getLabel of field
     *
     * @return string
     */
    public function getLabel()
    {
        return isset($this->options['label']) ? $this->options['label'] : ucfirst($this->getName());
    }
}
