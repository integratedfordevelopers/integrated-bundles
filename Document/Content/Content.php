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

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Metadata;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Relation;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;

use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\ChannelableInterface;
use Integrated\Common\Content\ExtensibleInterface;
use Integrated\Common\Content\ExtensibleTrait;
use Integrated\Common\Content\MetadataInterface;
use Integrated\Common\Content\ContentInterface;
use Integrated\Common\Content\RegistryInterface;

use Integrated\Common\Form\Mapping\Annotations as Type;

/**
 * Abstract base class for document types
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class Content implements ContentInterface, ExtensibleInterface, MetadataInterface, ChannelableInterface
{
    use ExtensibleTrait;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string the type of the ContentType
     */
    protected $contentType;

    /**
     * @var ArrayCollection
     */
    protected $relations;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var PublishTime
     * @Type\Field(type="integrated_publish_time")
     */
    protected $publishTime;

    /**
     * @var bool
     */
    protected $published = true;

    /**
     * @var bool
     * @Type\Field(type="checkbox")
     */
    protected $disabled = false;

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * @var Collection
     */
    protected $channels;

    /**
     * @var array
     */
    protected $customFields = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->relations = new ArrayCollection();
        $this->updatedAt = new \DateTime();
        $this->publishTime = new PublishTime();
        $this->channels = new ArrayCollection();
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
        if ($exist = $this->getRelation($relation->getRelationId())) {
            $exist->addReferences($relation->getReferences());
        } else {
            $this->relations->add($relation);
        }

        return $this;
    }

    /**
     * Add reference to relations collection
     *
     * @todo not compatible with latest relations version
     * @param ContentInterface $content
     * @throws \Exception
     */
    public function addReference(ContentInterface $content)
    {
        throw new \Exception('Method not longer supported');
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
     * @param $relationId
     * @return Relation|false
     */
    public function getRelation($relationId)
    {
        return $this->relations->filter(function ($relation) use ($relationId) {
            if ($relation instanceof Relation) {
                if ($relation->getRelationId() == $relationId) {
                    return true;
                }
            }

            return false;
        })->first();
    }

    /**
     * @param $relationType
     * @return ArrayCollection|false
     */
    public function getRelationsByRelationType($relationType)
    {
        return $this->relations->filter(function ($relation) use ($relationType) {
            if ($relation instanceof Relation) {
                if ($relation->getRelationType() == $relationType) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @param $relationType
     * @return array|bool
     */
    public function getReferencesByRelationType($relationType)
    {
        if ($relations = $this->getRelationsByRelationType($relationType)) {
            $references = array();

            /** @var Relation $relation */
            foreach ($relations as $relation) {
                $references = array_merge($references, $relation->getReferences()->toArray());
            }

            return $references;
        }

        return false;
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
     * Get the publish time of the document
     *
     * @return PublishTime
     */
    public function getPublishTime()
    {
        return $this->publishTime;
    }

    /**
     * Set the publish time of the document
     *
     * @param PublishTime $publishTime
     * @return $this
     */
    public function setPublishTime(PublishTime $publishTime)
    {
        $this->publishTime = $publishTime;
        return $this;
    }

    /**
     * Get the published of the document
     *
     * @deprecated
     * @return bool
     */
    public function getPublished()
    {
        return $this->isPublished();
    }

    /**
     * Get the published of the document
     *
     * @param bool $checkPublishTime
     * @return bool
     */
    public function isPublished($checkPublishTime = true)
    {
        $published = true;

        if ($checkPublishTime && $this->publishTime instanceof PublishTime) {
            $published = $this->publishTime->isPublished();
        }

        return ($published && !$this->disabled);
    }

    /**
     * Set the published of the document
     *
     * @param bool $published
     * @return $this
     */
    public function setPublished($published)
    {
        $this->published = $published;
        return $this;
    }

    /**
     * Get the disabled of the document
     *
     * @return bool
     */
    public function isDisabled()
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
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        if ($this->metadata === null) {
            $this->metadata = new Metadata();
        }

        return $this->metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadata(RegistryInterface $metadata = null)
    {
        if ($metadata !== null && !$metadata instanceof Metadata) {
            $metadata = new Metadata($metadata->toArray());
        }

        $this->metadata = $metadata;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannels(Collection $channels)
    {
        $this->channels->clear();
        $this->channels = new ArrayCollection();

        foreach ($channels as $channel) {
            $this->addChannel($channel); // type check
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannels()
    {
        return $this->channels->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function addChannel(ChannelInterface $channel)
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChannel(ChannelInterface $channel)
    {
        return $this->channels->contains($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function removeChannel(ChannelInterface $channel)
    {
        $this->channels->removeElement($channel);
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * @param array $customFields
     * @return $this
     */
    public function setCustomFields(array $customFields)
    {
        $this->customFields = $customFields;
        return $this;
    }

    /**
     * @param $field
     * @return bool
     */
    public function hasCustomField($field)
    {
        return array_key_exists($field, $this->customFields);
    }

    /**
     * @param mixed $field
     * @param mixed $value
     * @return $this
     */
    public function addCustomField($field, $value)
    {
        $this->customFields[$field] = $value;
        return $this;
    }

    /**
     * @param $field
     * @return mixed|null
     */
    public function getCustomField($field)
    {
        if ($this->hasCustomField($field)) {
            return $this->customFields[$field];
        }

        return null;
    }

    /**
     * @param $field
     * @return $this
     */
    public function removeCustomField($field)
    {
        if ($this->hasCustomField($field)) {
            unset($this->customFields[$field]);
        }

        return $this;
    }

    /**
     * updateUpdatedAtOnPreUpdate
     */
    public function updateUpdatedAtOnPreUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * updatePublishTimeOnPreUpdate
     */
    public function updatePublishTimeOnPreUpdate()
    {
        if (!$this->publishTime instanceof PublishTime) {
            return;
        }

        if (!$this->publishTime->getStartDate() instanceof \DateTime) {
            $this->publishTime->setStartDate($this->createdAt);
        }

        if (!$this->publishTime->getEndDate() instanceof \DateTime) {
            $this->publishTime->setEndDate(new \DateTime(PublishTime::DATE_MAX));
        }
    }
}
