<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @author bleek@cross-solution.de
 * @license   MIT
 */

namespace Jobs\Controller;

use Core\Entity\PermissionsInterface;
use Jobs\Entity\Status;
use Jobs\Repository;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Stdlib\AbstractOptions;
use Zend\Http\PhpEnvironment\Response;

/**
 * Handles rendering the job in formular and in preview mode
 *
 * Class TemplateController
 * @package Jobs\Controller
 */
class TemplateController extends AbstractActionController
{

    /**
     * @var Repository\Job $jobRepository
     */
    private $jobRepository;

    /**
     * @var AbstractOptions
     */
    protected $config;

    public function __construct(Repository\Job $jobRepository, AbstractOptions $config)
    {
        $this->jobRepository = $jobRepository;
        $this->config = $config;
    }

    /**
     * Handles the job opening template in preview mode
     *
     * @return ViewModel
     * @throws \RuntimeException
     */
    public function viewAction()
    {
        $id = $this->params()->fromQuery('id');
        /* @var \Jobs\Entity\Job $job */
        $job = $this->jobRepository->find($id);
        $services             = $this->serviceLocator;
        $mvcEvent             = $this->getEvent();
        $applicationViewModel = $mvcEvent->getViewModel();

        /* @var \Auth\Entity\User $user */
        $user = $this->auth()->getUser();

        /* @var \Zend\View\Model\ViewModel $model */
        $model = $services->get('Jobs/viewModelTemplateFilter')->__invoke($job);

        if (
            Status::ACTIVE == $job->getStatus() or
            $job->getPermissions()->isGranted($user, PermissionsInterface::PERMISSION_VIEW) or
            $this->auth()->isAdmin()
        ) {
            $applicationViewModel->setTemplate('iframe/iFrameInjection');
        }elseif(Status::EXPIRED == $job->getStatus() or  Status::INACTIVE == $job->getStatus()) {
            $this->response->setStatusCode(Response::STATUS_CODE_410);
            $model->setTemplate('jobs/error/expired');
            $model->setVariables(
                [
                    'job'=>$job,
                    'message', 'the job posting you were trying to open, was inactivated or has expired'
                ]
            );
        } else {
            // there is a special handling for 404 in ZF2
            $this->response->setStatusCode(Response::STATUS_CODE_404);
            $model->setVariable('message', 'job is not available');
        }
        return $model;
    }

    /**
     * Handles the job opening template in formular mode.
     *
     * All template forms are sending the ID of a job posting and an identifier of the sending
     * form.
     *
     * @return ViewModel
     */
    protected function editTemplateAction()
    {
        $id = $this->params('id');
        $formIdentifier=$this->params()->fromQuery('form');
        $job = $this->jobRepository->find($id);

        /** @var \Zend\Http\Request $request */
        $request              = $this->getRequest();
        $isAjax               = $request->isXmlHttpRequest();
        $services             = $this->serviceLocator;
        $viewHelperManager    = $services->get('ViewHelperManager');
        $mvcEvent             = $this->getEvent();
        $applicationViewModel = $mvcEvent->getViewModel();
        $forms                = $services->get('FormElementManager');

        /** @var \Jobs\Form\JobDescriptionTemplate $formTemplate */
        $formTemplate         = $forms->get(
            'Jobs/Description/Template',
            array(
            'mode' => $job->id ? 'edit' : 'new'
            )
        );

        $formTemplate->setParam('id', $job->id);
        $formTemplate->setParam('applyId', $job->applyId);

        $formTemplate->setEntity($job);

        if (isset($formIdentifier) && $request->isPost()) {
            // at this point the form get instantiated and immediately accumulated

            $instanceForm = $formTemplate->get($formIdentifier);
            if (!isset($instanceForm)) {
                throw new \RuntimeException('No form found for "' . $formIdentifier . '"');
            }

            // the id is part of the postData, but it never should be altered
            $postData = $request->getPost();

            unset($postData['id']);
            unset($postData['applyId']);

            $instanceForm->setData($postData);
            if ($instanceForm->isValid()) {
                $this->serviceLocator->get('repositories')->persist($job);
            }
        }

        $model = $services->get('Jobs/ViewModelTemplateFilter')->__invoke($formTemplate);

        if (!$isAjax) {
            $basePath   = $viewHelperManager->get('basepath');
            $headScript = $viewHelperManager->get('headscript');
            $headScript->appendFile($basePath->__invoke('/Core/js/core.forms.js'));

            $headStyle = $viewHelperManager->get('headstyle');
            $headStyle->prependStyle('form > input {
            color: inherit !important; margin:inherit !important;
            padding:inherit !important; border:0 !important; cursor:pointer !important; letter-spacing:inherit !important;
            line-height: inherit !important;
             font-size: inherit !important;
}
'
            );
        } else {
            return new JsonModel(array('valid' => true));
        }
        $applicationViewModel->setTemplate('iframe/iFrameInjection');
        return $model;
    }
}
