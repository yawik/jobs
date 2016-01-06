<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2015 Cross Solution (http://cross-solution.de)
 * @license       MIT
 */

namespace Jobs\Factory\Controller;

use Jobs\Controller\IndexController;
use Jobs\Form\ListFilter;
use Jobs\Repository;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{

    /**
     * Injects all needed services into the IndexController
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return IndexController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ControllerManager $serviceLocator */
        $serviceLocator = $serviceLocator->getServiceLocator();

        $searchForm = $serviceLocator->get('forms')
            ->get('Jobs/ListFilter', [ 'fieldset' => 'Jobs/ListFilterPersonalFieldset' ]);

        /**
         * @var $jobRepository Repository\Job
         */
        $jobRepository = $serviceLocator->get('repositories')->get('Jobs/Job');

        return new IndexController($jobRepository, $searchForm);
    }
}
