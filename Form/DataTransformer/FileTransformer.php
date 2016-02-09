<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Storage;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class FileTransformer implements DataTransformerInterface
{
    /**
     * @param Storage | null $file
     * @return array
     */
    public function transform($file)
    {
        return [
            'file' => $file,
        ];
    }

    /**
     * @param array $value
     * @return Storage | null
     */
    public function reverseTransform($value)
    {
        if (!isset($value['file'])) {
            return null;
        }

        return $value['file'];
    }
}
