<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Controller\Content;

use Symfony\Bundle\TwigBundle\TwigEngine;

use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Company;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class CompanyController
{
    /**
     * @var TwigEngine
     */
    protected $templating;

    /**
     * @var ThemeManager
     */
    protected $themeManager;

    /**
     * @param TwigEngine $templating
     * @param ThemeManager $themeManager
     */
    public function __construct(TwigEngine $templating, ThemeManager $themeManager)
    {
        $this->templating = $templating;
        $this->themeManager = $themeManager;
    }

    /**
     * @param Company $company
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Company $company)
    {
        return $this->templating->renderResponse($this->themeManager->locateTemplate('content/Company/default.html.twig'), [
            'company' => $company,
        ]);
    }
}
