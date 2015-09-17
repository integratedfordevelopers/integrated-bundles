<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\DataFixtures\MongoDB;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;

use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Nelmio\Alice\Fixtures;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Finder\Finder;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class LoadFixtureData extends ContainerAware implements FixtureInterface
{
    /**
     * @var string
     */
    protected $path = __DIR__;

    /**
     * @var string
     */
    protected $locale = 'en_US';

    /**
     * @var MetadataFactoryInterface
     */
    private $metadata = null;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        $files = array();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach (Finder::create()->in($this->path . DIRECTORY_SEPARATOR  . 'alice')->name('*.yml')->sortByName() as $file) {
            $files[] = $file->getRealpath();
        }

        Fixtures::load($files, $manager, ['providers' => [$this], 'locale' => $this->locale]);
    }

    /**
     * create a list of field based on the class.
     *
     * The field list will always reflect the current field configuration of the
     * class. It is possible to supply a list of fields that are required and that
     * should be ignored.
     *
     * @param string   $class
     * @param string[] $required  set the required flag for these fields
     * @param string[] $filter    a list of field that are white or black listed basted on $blacklist
     * @param bool     $blacklist if true then $filter is a blacklist and a white list if false
     *
     * @return Field[]
     */
    public function classfields($class, array $required = [], array $filter = [], $blacklist = true)
    {
        $fields = [];

        if (!$metadata = $this->getMetadata()->getMetadata($class)) {
            return $fields;
        }

        $required = array_map('strtolower', $required);
        $filter = array_map('strtolower', $filter);
        $blacklist = (bool) $blacklist;

        if (!$blacklist) {
            $filter = array_merge($filter, $required);
        }

        foreach ($metadata->getFields() as $field) {
            if ($blacklist === in_array(strtolower($field->getName()), $filter)) {
                continue;
            }

            $fields[$field->getName()] = (new Field())
                ->setName($field->getName())
                ->setType($field->getType())
                ->setOptions($field->getOptions() + ['required' => in_array(strtolower($field->getName()), $required)]);
        }

        return $fields;
    }

    /**
     * @param array $elements
     * @return ArrayCollection
     */
    public function arrayCollection(array $elements)
    {
        return new ArrayCollection($elements);
    }

    /**
     * @return MetadataFactoryInterface
     */
    protected function getMetadata()
    {
        if ($this->metadata === null) {
            $this->metadata = $this->container->get('integrated_content.metadata.factory');
        }

        return $this->metadata;
    }
}