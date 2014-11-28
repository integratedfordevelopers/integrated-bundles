<?php

/*
* This file is part of the Integrated package.
*
* (c) e-Active B.V. <integrated@e-active.nl>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Integrated\Common\Block\BlockInterface;
use Integrated\Bundle\WebsiteBundle\Document\Block\Block;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class BlockController extends Controller
{
    /**
     * Lists all the Block documents.
     *
     * @Template
     */
    public function indexAction(Request $request)
    {
        $builder = $this->getDocumentManager()->createQueryBuilder('IntegratedWebsiteBundle:Block\Block');

        $pagination = $this->getPaginator()->paginate(
            $builder,
            $request->query->get('page', 1),
            20
        );

        return [
            'blocks'  => $pagination,
            'factory' => $this->getFactory(),
        ];
    }

    /**
     * @Template
     */
    public function newAction(Request $request)
    {
        $class = $request->get('class');

        $block = class_exists($class) ? new $class : null;

        if (!$block instanceof BlockInterface) {
            throw $this->createNotFoundException(sprintf('Invalid block "%s"', $class));
        }

        $form = $this->createCreateForm($block);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $dm = $this->getDocumentManager();

            $dm->persist($block);
            $dm->flush();

            $this->get('braincrafted_bootstrap.flash')->success('Block created');

            return $this->redirect($this->generateUrl('integrated_website_block_index'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Template
     */
    public function editAction(Request $request, Block $block)
    {
        $form = $this->createEditForm($block);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $this->getDocumentManager()->flush();

            $this->get('braincrafted_bootstrap.flash')->success('Block updated');

            return $this->redirect($this->generateUrl('integrated_website_block_index'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Template
     */
    public function deleteAction(Request $request, Block $block)
    {
        $form = $this->createDeleteForm($block->getId());
        $form->handleRequest($request);

        if ($form->isValid()) {

            $dm = $this->getDocumentManager();

            $dm->remove($block);
            $dm->flush();

            $this->get('braincrafted_bootstrap.flash')->success('Block deleted');

            return $this->redirect($this->generateUrl('integrated_website_block_index'));
        }

        return [
            'block' => $block,
            'form'  => $form->createView(),
        ];
    }

    /**
     * @param BlockInterface $block
     * @return \Symfony\Component\Form\Form
     */
    protected function createCreateForm(BlockInterface $block)
    {
        $class = get_class($block);

        $form = $this->createForm(
            $this->get('integrated_website.form.block.type'),
            $block,
            [
                'action' => $this->generateUrl('integrated_website_block_new', ['class' => $class]),
                'method' => 'POST',
                'data_class' => $class,
            ]
        );

        $form->add('submit', 'submit', ['label' => 'Save']);

        return $form;
    }

    /**
     * @param BlockInterface $block
     * @return \Symfony\Component\Form\Form
     */
    protected function createEditForm(BlockInterface $block)
    {
        $form = $this->createForm(
            $this->get('integrated_website.form.block.type'),
            $block,
            [
                'action' => $this->generateUrl('integrated_website_block_edit', ['id' => $block->getId()]),
                'method' => 'PUT',
                'data_class' => get_class($block),
            ]
        );

        $form->add('submit', 'submit', ['label' => 'Save']);

        return $form;
    }

    /**
     * @param string $id
     * @return \Symfony\Component\Form\Form
     */
    protected function createDeleteForm($id)
    {
        $builder = $this->createFormBuilder();

        $builder->setAction($this->generateUrl('integrated_website_block_delete', ['id' => $id]));
        $builder->setMethod('DELETE');
        $builder->add('submit', 'submit', ['label' => 'Delete', 'attr' => ['class' => 'btn-danger']]);

        return $builder->getForm();
    }

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->get('doctrine_mongodb')->getManager();
    }

    /**
     * @return \Knp\Component\Pager\Paginator
     */
    protected function getPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * @return \Integrated\Common\Form\Mapping\MetadataFactoryInterface
     */
    protected function getFactory()
    {
        return $this->get('integrated_website.metadata.factory');
    }
}
