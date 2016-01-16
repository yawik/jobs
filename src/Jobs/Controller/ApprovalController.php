<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2015 Cross Solution (http://cross-solution.de)
 * @license       MIT
 */

/**
 * ActionController. Lists jobs of all organizations
 */
namespace Jobs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container as Session;
use Zend\View\Model\JsonModel;
use Auth\Entity\User;
use Jobs\Repository;
use Jobs\Form\ListFilter;
use Zend\View\Model\ViewModel;

/**
 * Handles the job listing for recruiters.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @author Mathias Weitz <weitz@cross-solution.de>
 * @author Carsten Bleek <bleek@cross-solution.de>
 *
 * @method \Auth\Controller\Plugin\Auth auth()
 * @method \Acl\Controller\Plugin\Acl acl()
 * @method \Core\Controller\Plugin\CreatePaginator paginator(string $repositoryName, array $defaultParams = array(), bool $usePostParams = false)
 */
class ApprovalController extends AbstractActionController
{

    /**
     * @var Repository\Job $jobRepository
     */
    private $jobRepository;

    /**
     * Formular for searching job postings
     *
     * @var ListFilter $searchForm
     */
    private $searchForm;


    /**
     * Construct the jobboard controller
     *
     * @param Repository\Job $jobRepository
     * @param ListFilter $searchForm
     */
    public function __construct(Repository\Job $jobRepository, ListFilter $searchForm)
    {
        $this->jobRepository = $jobRepository;
        $this->searchForm = $searchForm;
    }

    /**
     * attaches further Listeners for generating / processing the output
     *
     * @return $this
     */
    public function attachDefaultListeners()
    {
        parent::attachDefaultListeners();

        $serviceLocator  = $this->getServiceLocator();
        $defaultServices = $serviceLocator->get('DefaultListeners');
        $events          = $this->getEventManager();
        $events->attach($defaultServices);

        return $this;
    }

    /**
     * @return ViewModel
     */
    public function listOpenJobsAction()
    {
        /* @var $request \Zend\Http\Request */
        $request     = $this->getRequest();
        $params      = $request->getQuery();
        $jsonFormat  = 'json' == $params->get('format');
        $isRecruiter = $this->acl()->isRole(User::ROLE_RECRUITER);

        if (!$jsonFormat && !$request->isXmlHttpRequest()) {
            $session       = new Session('Jobs\Index');
            $sessionKey    = $this->auth()->isLoggedIn() ? 'userParams' : 'guestParams';
            $sessionParams = $session[$sessionKey];

            if ($sessionParams) {
                foreach ($sessionParams as $key => $value) {
                    $params->set($key, $params->get($key, $value));
                }
            } elseif ($isRecruiter) {
                $params->set('by', 'me');
            }
            /* @var $filterForm \Jobs\Form\ListFilter */
            $session[$sessionKey] = $params->toArray();

            $this->searchForm->bind($params);

        }

        if (!isset($params['sort'])) {
            $params['sort'] = '-date';
        }

        $paginator = $this->paginatorservice('Jobs/Admin', $params);

        $return = array(
            'by'   => $params->get('by', 'all'),
            'jobs' => $paginator,
            'showPendingJobs' => true,
        );
        if (isset($this->searchForm)) {
            $return['filterForm'] = $this->searchForm;
        }

        $model = new ViewModel();
        $model->setVariables($return);
        $model->setTemplate('jobs/index/approval');
        return $model;
    }
}
