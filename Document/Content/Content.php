<?php

/*
* This file is part of the Integrated package.
*
* (c) e-Active B.V. <integrated@e-active.nl>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Metadata;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;

use Integrated\Common\Content\ExtensibleInterface;
use Integrated\Common\Content\ExtensibleTrait;
use Integrated\Common\Content\MetadatableInterface;
use Integrated\Common\Content\ContentInterface;

use Integrated\Common\Content\MetadataInterface;
use Integrated\Common\ContentType\Mapping\Annotations as Type;

/**
 * Abstract base class for document types
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 *
 * @ODM\Document(collection="content", indexes={@ODM\Index(keys={"class"="asc"})})
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="class")
 */
class Content implements ContentInterface, ExtensibleInterface, MetadatableInterface
{
	use ExtensibleTrait;

    /**
     * @var string
     * @ODM\Id(strategy="UUID")
     */
    protected $id;

    /**
     * @var string the type of the ContentType
     * @ODM\String
	 * @ODM\Index
     */
    protected $contentType;

    /**
     * @var ArrayCollection
     * @ODM\EmbedMany(targetDocument="Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation")
     */
    protected $relations;

    /**
     * @var \DateTime
     * @ODM\Date
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ODM\Date
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     * @ODM\Date
     */
    protected $publishedAt;

    /**
     * @var bool
     * @ODM\Boolean
     * @Type\Field(type="checkbox")
     */
    protected $disabled;

    /**
     * @var Metadata
	 * @ODM\EmbedOne(targetDocument="Integrated\Bundle\ContentBundle\Document\Content\Embedded\Metadata")
     */
    protected $metadata = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->relations = new ArrayCollection();
    }

    /**
     * Get the id of the document
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the id of the document
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Get the relations of the document
     *
     * @return ArrayCollection
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Set the relations of the document
     *
     * @param Collection $relations
     * @return $this
     */
    public function setRelations(Collection $relations)
    {
        foreach ($relations as $relation) {
            // TODO: should we check if relation instanceof Relation
            $this->addRelation($relation);
        }

        return $this;
    }

    /**
     * Add relation to relations collection
     *
     * @param Relation $relation
     * @return $this
     */
    public function addRelation(Relation $relation)
    {
        if ($exist = $this->getRelation($relation->getContentType())) {
            $exist->addReferences($relation->getReferences());
        } else {
            $this->relations->add($relation);
        }

        return $this;
    }

    /**
     * Add reference to relations collection
     *
     * @param ContentInterface $content
     * @return $this
     */
    public function addReference(ContentInterface $content)
    {
        $relation = new Relation();
        $relation->setContentType($content->getContentType());
        $relation->addReference($content);

        $this->addRelation($relation);

        return $this;
    }

    /**
     * Remove relation from relations collection
     *
     * @param Relation $relation
     * @return $this
     */
    public function removeRelation(Relation $relation)
    {
        $this->relations->removeElement($relation);
        return $this;
    }

    /**
     * @param $contentType
     * @return Relation|false
     */
    public function getRelation($contentType)
    {
        return $this->relations->filter(function($relation) use($contentType) {
            if ($relation instanceof Relation) {
                if ($relation->getContentType() == $contentType) {
                    return true;
                }
            }

			return false;
        })->first();
    }

    /**
     * Get the createdAt of the document
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the createdAt of the document
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get the updatedAt of the document
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set the updatedAt of the document
     *
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get the publishedAt of the document
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set the publishedAt of the document
     *
     * @param \DateTime $publishedAt
     * @return $this
     */
    public function setPublishedAt(\DateTime $publishedAt)
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * Get the disabled of the document
     *
     * @return bool
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set the disabled of the document
     *
     * @param bool $disabled
     * @return $this
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        return $this;
    }

	/**
	 * @inheritdoc
	 */
	public function getMetadata()
	{
		if ($this->metadata === null) {
			$this->metadata = new Metadata();
		}

		return $this->metadata;
	}

	/**
	 * @inheritdoc
	 */
	public function setMetadata(MetadataInterface $metadata = null)
	{
		if ($metadata !== null && !$metadata instanceof Metadata) {
			$metadata = new Metadata($metadata->toArray());
		}

		$this->metadata = $metadata;
		return $this;
	}
}