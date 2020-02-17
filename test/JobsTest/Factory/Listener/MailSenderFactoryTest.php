<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2016 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace JobsTest\Factory\Listener;

use PHPUnit\Framework\TestCase;

use Jobs\Factory\Listener\MailSenderFactory;

/**
 * Tests for MailSender factory
 *
 * @covers \Jobs\Factory\Listener\MailSenderFactory
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @group Jobs
 * @group Jobs.Factory
 * @group Jobs.Factory.Listener
 */
class MailSenderFactoryTest extends TestCase
{

    /**
     * @testdox Implements \Laminas\ServiceManager\FactoryInterface
     */
    public function testImplementsFactoryInterface()
    {
        $this->assertInstanceOf('\Laminas\ServiceManager\Factory\FactoryInterface', new MailSenderFactory());
    }

    public function testCreatesAMailSenderListenerWithAllDependencies()
    {
        $mailService = $this->getMockBuilder('\Core\Mail\MailService')->disableOriginalConstructor()->getMock();
        $jobsOptions = new \Jobs\Options\ModuleOptions(array(
            'multipostingApprovalMail' => 'test@email'
        ));
        $coreOptions = new \Core\Options\ModuleOptions(array(
            'siteName' => 'YAWIK Test'
        ));

        $services = $this->getMockBuilder('\Laminas\ServiceManager\ServiceManager')
                         ->disableOriginalConstructor()
                         ->getMock();

        $services->expects($this->exactly(3))
                 ->method('get')
                 ->withConsecutive(
                     array('Core/MailService'),
                     array('Jobs/Options'),
                     array('Core/Options')
                 )
                 ->will($this->onConsecutiveCalls($mailService, $jobsOptions, $coreOptions));

        $expectedOptions = array(
            'siteName' => $coreOptions->getSiteName(),
            'adminEmail' => $jobsOptions->getMultipostingApprovalMail()
        );

        $target = new MailSenderFactory();
        $listener = $target->__invoke($services, 'irrelevant');

        $this->assertInstanceOf('\Jobs\Listener\MailSender', $listener);
        $this->assertAttributeSame($mailService, 'mailer', $listener);
        $this->assertAttributeEquals($expectedOptions, 'options', $listener);
    }
}
