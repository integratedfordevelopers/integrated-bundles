<?php

namespace Integrated\Bundle\StorageBundle\Storage\Reflection;

use Integrated\Bundle\StorageBundle\Storage\Reflection\Document\FactoryProperty;
use Integrated\Bundle\StorageBundle\Storage\Reflection\Document\PropertyInterface;

use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class StorageReflection
{
    /**
     * @const The storage class to look for
     */
    const StorageClass = 'Integrated\Bundle\StorageBundle\Document\Embedded\Storage';

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array|null
     */
    protected $properties;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * @return PropertyInterface[]
     */
    public function getStorageProperties()
    {
        if (null === $this->properties) {
            // Prevent additional lookup when none is found
            $this->properties = [];

            $reader = new AnnotationReader();
            $reflection = new \ReflectionClass($this->className);

            foreach ($reflection->getProperties() as $property) {
                foreach ($reader->getPropertyAnnotations($property) as $annotation) {
                    if (FactoryProperty::isValid($annotation)) {
                        if (self::StorageClass == $annotation->targetDocument) {
                            // Stuff
                            $this->properties[] = FactoryProperty::factory($property, $annotation);
                        }
                    }
                }
            }
        }

        return $this->properties;
    }
}
