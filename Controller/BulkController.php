<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;

use Exception;

use Integrated\Bundle\ContentBundle\Bulk\BuildState;
use Integrated\Bundle\ContentBundle\Form\Type\Bulk\ActionsFormType;
use Integrated\Bundle\ContentBundle\Form\Type\SelectionFormType;
use Integrated\Bundle\ContentBundle\Document\Bulk\BulkAction;
use Integrated\Bundle\ContentBundle\Bulk\ContentProvider;
use Integrated\Bundle\ContentBundle\Bulk\ActionProvider;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Patrick Mestebeld <patrick@e-active.nl>
 */
class BulkController extends Controller
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var ContentProvider
     */
    protected $contentProvider;

    /**
     * @var ActionProvider
     */
    protected $actionProvider;

    /**
     * BulkController constructor.
     * @param DocumentManager $dm
     * @param ContentProvider $contentProvider
     * @param ActionProvider $actionProvider
     * @param ContainerInterface $container
     */
    public function __construct(
        DocumentManager $dm,
        ContentProvider $contentProvider,
        ActionProvider $actionProvider,
        ContainerInterface $container
    ) {
        $this->dm = $dm;
        $this->contentProvider = $contentProvider;
        $this->actionProvider = $actionProvider;
        $this->container = $container;
    }

    /**
     * @Template()
     * @param Request $request
     * @return array | RedirectResponse
     */
    public function selectAction(Request $request)
    {
        // Fetch Content selection.
        $limit = 1000;
        $contents = $this->contentProvider->getContentFromSolr($request, $limit + 1);

        if (!$contents) {
            return $this->redirectToRoute('integrated_content_content_index', $request->query->all());
        }

        $form = $this->createForm(SelectionFormType::class, null, ['content' => array_slice($contents, 0, $limit)]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($bulkAction = $this->dm->getRepository(BulkAction::class)->find($request->get('bulkid'))) {
                $bulkAction->setSelection($data['selection']);
            } else {
                $bulkAction = new BulkAction($data['selection']);
                $this->dm->persist($bulkAction);
                $request->query->add(['bulkid' => $bulkAction->getId()]);
            }

            $this->dm->flush();
            return $this->redirectToRoute('integrated_content_bulk_configure', $request->query->all());
        }

        return [
            'filters' => $request->query->all(),
            'contents' => $contents,
            'limit' => $limit,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function configureAction(Request $request)
    {
        //Fetch BulkAction by ID
        $bulkAction = $this->dm->getRepository(BulkAction::class)->find($request->get('bulkid'));

        if (!$bulkAction instanceof BulkAction) {
            return $this->redirectToRoute('integrated_content_content_index', $request->query->all());
        }

        //Set actions to BulkAction
        $bulkAction->addActions($this->actionProvider->getActions($bulkAction->getActions()));

        $form = $this->createForm(ActionsFormType::class, $bulkAction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bulkAction = $form->getData();

            if (!$bulkAction instanceof BulkAction) {
                return $this->redirectToRoute('integrated_content_content_index', $request->query->all());
            }

            $bulkAction->setState(BuildState::CONFIGURED);
            $this->dm->flush();

            return $this->redirectToRoute('integrated_content_bulk_confirm', $request->query->all());
        }

        return [
            'filters' => $request->query->all(),
            'selection' => $bulkAction->getSelection(),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Template()
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function confirmAction(Request $request)
    {
        $bulkAction = $this->dm->getRepository(BulkAction::class)->find($request->get('bulkid'));

        if (!$bulkAction instanceof BulkAction) {
            return $this->redirectToRoute('integrated_content_content_index', $request->query->all());
        }

        // Check if bulk action state is correct and redirect if not.
        if ($bulkAction->getState() !== BuildState::CONFIGURED) {
            $this->addFlash('danger', 'This bulk action has not completed all steps correctly.');
            $request->query->remove('bulkid');
            return $this->redirectToRoute('integrated_content_content_index', $request->query->all());
        }

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        // If form submitted try to execute all bulk action.
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $bulkAction->setState(BuildState::CONFIRMED);
                $bulkAction->executeAll();
                $this->dm->flush();

                $this->addFlash('success', 'It seems all bulk actions were executed successfully! :)');
                $request->query->remove('bulkid');
                return $this->redirectToRoute('integrated_content_content_index', $request->query->all());
            } catch (Exception $e) {
                $this->addFlash(
                    'danger',
                    'Whoops! It seems something went wrong during the execution of this bulk action! The following error has given: "' . $e->getMessage() . '"'
                );
            }
        }

        return [
            'filters' => $request->query->all(),
            'contents' => $bulkAction->getSelection(),
            'actions' => $bulkAction->getActions(),
            'form' => $form->createView(),
        ];
    }
}
