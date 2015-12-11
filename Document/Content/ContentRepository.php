<?php
namespace Integrated\Bundle\ContentBundle\Document\Content;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;

/**
 * Class ContentRepository
 *
 * @author Vasil Pascal <developer.optimum@gmail.com>
 */
class ContentRepository extends DocumentRepository
{
    /**
     * Get items which have the current document linked
     *
     * @param Content $content
     * @param Relation $relation
     * @return array
     */
    public function getUsedBy(Content $content, Relation $relation = null)
    {
        $query =  $this->createQueryBuilder()
            ->field('relations.relationId')
            ->equals($relation->getId());

        if ($relation) {
            $query->field('relations.references.$id')->equals($content->getId());
        }

        return $query;
    }
}