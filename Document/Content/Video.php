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

use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Form\Mapping\Annotations as Type;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Document type Image
 *
 * @author Johnny Borg <johnny@e-active.nl>
 *
 * @Type\Document("Video")
 */
class Video extends File
{
    /**
     * @var StorageInterface
     * @Type\Field(type="integrated_file_dropzone")
     * @Assert\File(mimeTypes="video/*")
     */
    protected $file;
}
