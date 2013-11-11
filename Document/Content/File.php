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

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Integrated\Common\ContentType\Mapping\Annotations as Content;

/**
 * Document type File
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 * @ODM\Document(collection="content")
 * @Content\Document("File")
 */
class File extends AbstractContent
{
    /**
     * @var array
     * @ODM\Hash
     * @Content\Field(type="translatable_text")
     */
    protected $title = array();

    /**
     * @var string
     * @ODM\String
     * @Content\Field(type="file")
     */
    protected $file;

    /**
     * @var array
     * @ODM\Hash
     * @Content\Field(type="translatable_textarea")
     */
    protected $description = array();

    /**
     * Get the title of the document
     *
     * @return array
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title of the document
     *
     * @param array $title
     * @return $this
     */
    public function setTitle(array $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the file of the document
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the file of the document
     *
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Get the description of the document
     *
     * @return array
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description of the document
     *
     * @param array $description
     * @return $this
     */
    public function setDescription(array $description)
    {
        $this->description = $description;
        return $this;
    }
}