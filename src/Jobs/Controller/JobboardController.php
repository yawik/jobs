<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** ActionController of Jobs */
namespace Jobs\Controller;

use Jobs\Form\ListFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container as Session;
use Jobs\Repository;
use Zend\View\Model\ViewModel;

/**
 * @method \Auth\Controller\Plugin\Auth auth()
 * @method \Core\Controller\Plugin\CreatePaginatorService paginatorService()
 *
 * Controller for jobboard actions
 */
class JobboardController extends AbstractActionController
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
     * @return $this
     */
    public function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
        $serviceLocator = $this->serviceLocator;
        $defaultServices = $serviceLocator->get('DefaultListeners');
        $events          = $this->getEventManager();
        $events->attach($defaultServices);
        return $this;
    }

    /**
     * List jobs
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        /* @var \Zend\Http\Request $request */
        $request          = $this->getRequest();
        $params           = $request->getQuery();
        $jsonFormat       = 'json' == $request->getQuery()->get('format');
        $event            = $this->getEvent();
        $routeMatch       = $event->getRouteMatch();
        $matchedRouteName = $routeMatch->getMatchedRouteName();
        $url              = $this->url()->fromRoute($matchedRouteName, array(), array('force_canonical' => true));

        if (!$jsonFormat && !$request->isXmlHttpRequest()) {
            $session = new Session('Jobs\Index');
            $sessionKey = $this->auth()->isLoggedIn() ? 'userParams' : 'guestParams';
            $sessionParams = $session[$sessionKey];
            if ($sessionParams) {
                foreach ($sessionParams as $key => $value) {
                    $params->set($key, $params->get($key, $value));
                }
            }
            $session[$sessionKey] = $params->toArray();

            $this->searchForm->bind($params);
        }

        $params = $params->get('params', []);

        if (isset($params['l']['data']) &&
            isset($params['l']['name']) &&
            !empty($params['l']['name'])) {
            /* @var \Geo\Form\GeoText $geoText */
            $geoText = $this->searchForm->get('params')->get('l');

            $geoText->setValue($params['l']);
            $params['location'] = $geoText->getValue('entity');
        }

        if (!isset($params['sort'])) {
            $params['sort']='-date';
        }

        $this->searchForm->setAttribute('action', $url);

        $params['by'] = "guest";

        $paginator = $this->paginator('Jobs/Board', $params);

        $options = $this->searchForm->getOptions();
        $options['showButtons'] = false;
        $this->searchForm->setOptions($options);
        
        $return = array(
            'by' => $params['by'],
            'jobs' => $paginator,
            'filterForm' => $this->searchForm
        );
        $model = new ViewModel($return);

        return $model;
    }
}
