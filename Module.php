<?php
/**
 * Cross Applicant Management
 * Auth Module Bootstrap
 *
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

namespace Jobs;


use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;

/**
 * Bootstrap class of the Core module
 * 
 */
class Module implements ConsoleUsageProviderInterface
{

    public function getConsoleUsage(Console $console)
    {
        return array(
            'Manipulation of jobs database',
            'jobs generatekeywords [--filter=]' => '(Re-)Generates keywords for all jobs.',
            array('--filter=JSON', "available keys:\n"
                                  ."- 'before:ISODate' -> only jobs before the given date\n"
                                  ."- 'after':ISODate' -> only jobs after the given date\n"
                                  ."- 'title':String -> exakt title to match or if starting with '/' -> MongoRegex\n"
                                  ."- 'limit':INT -> Limit result."),
        );
    }
    
    /**
     * Loads module specific configuration.
     * 
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Loads module specific autoloader configuration.
     * 
     * @return array
     */
    public function getAutoloaderConfig()
    {
        
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
   
    
}
