<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @author cbleek
 * @license   AGPLv3
 */

namespace Jobs\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Jobs\Options\ModuleOptions;

/**
 * Class ModuleOptionsFactory
 * @package Jobs\Factory
 */
class ModuleOptionsFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ModuleOptions
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        $jobs_options = isset($config['jobs_options']) ? $config['jobs_options'] : array();

        if (!array_key_exists('multipostingApprovalMail', $jobs_options) || '' ==  trim($jobs_options['multipostingApprovalMail'])) {
            $coreOptions = $serviceLocator->get('Core/Options');
            $jobs_options['multipostingApprovalMail'] = $coreOptions->getSystemMessageEmail();
        }

        return new ModuleOptions($jobs_options);
    }
}
