<?php

/**
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class ArticleListBlockController extends Controller
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
