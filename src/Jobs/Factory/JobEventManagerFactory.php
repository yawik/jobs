<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2016 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace Jobs\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factors the JobEventManager which is used to trigger Job Events.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @since 0.19
 */
class JobEventManagerFactory implements FactoryInterface
{
    protected $identifiers = array(
        'Jobs',
        'Jobs/Events',
    );

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $events \Zend\EventManager\EventManagerInterface */
        $events = $serviceLocator->get('EventManager');

        $events->setEventClass('\Jobs\Listener\Events\JobEvent');
        $events->setIdentifiers($this->identifiers);

        return $events;
    }
}
