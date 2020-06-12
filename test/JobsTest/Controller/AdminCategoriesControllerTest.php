<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright https://yawik.org/COPYRIGHT.php
 */

/** */
namespace JobsTest\Controller;

use PHPUnit\Framework\TestCase;

use Core\Form\Tree\ManagementForm;
use Core\Form\View\Helper\SummaryForm;
use Core\Repository\AbstractRepository;
use Core\Repository\RepositoryService;
use CoreTestUtils\TestCase\ServiceManagerMockTrait;
use CoreTestUtils\TestCase\TestInheritanceTrait;
use Jobs\Controller\AdminCategoriesController;
use Jobs\Entity\Category;
use Jobs\Form\CategoriesContainer;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\View\Model\JsonModel;

/**
 * Tests for Jobs\Controller\AdminCategoriesController
 *
 * @covers \Jobs\Controller\AdminCategoriesController
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 *
 */
class AdminCategoriesControllerTest extends TestCase
{
    use TestInheritanceTrait, ServiceManagerMockTrait;

    private $target = AdminCategoriesController::class;

    private $inheritance = [ AbstractActionController::class ];

    private function getConfiguredServiceManager($rootExists=true, $postRequest = false)
    {
        $form = $this
            ->getMockBuilder(CategoriesContainer::class)
            ->disableOriginalConstructor()
            ->setMethods(['setEntity', 'getForm'])
            ->getMock();
        $form->expects($this->exactly(3))->method('setEntity')
            ->withConsecutive(
                [$this->isInstanceOf(Category::class), 'professions'],
                [$this->isInstanceOf(Category::class), 'employmentTypes'],
                [$this->isInstanceOf(Category::class), 'industries']
            );

        $sm = $this->getServiceManagerMock();
        if ($postRequest) {
            $subForm = $this->getMockBuilder(ManagementForm::class)->disableOriginalConstructor()
                ->setMethods(['setData', 'isValid', 'bind', 'setRenderMode', 'getObject', 'getMessages'])
                ->getMock();
            $subForm->expects($this->once())->method('setData');
            $subForm->expects($this->once())->method('isValid')->willReturn(true);
            $category = new Category('test');
            $subForm->expects($this->exactly(2))->method('getObject')->willReturn($category);
            $subForm->expects($this->once())->method('bind')->with($category);

            $form->expects($this->once())->method('getForm')->with('testFormId')->willReturn($subForm);

            $helper = $this->getMockBuilder(SummaryForm::class)->disableOriginalConstructor()
                ->setMethods(['__invoke'])->getMock();
            $helper->expects($this->once())->method('__invoke')->with($subForm);

            $viewHelpers = $this->createPluginManagerMock(['summaryForm' => $helper], $sm);
        } else {
            $viewHelpers = $this->createPluginManagerMock([], $sm);
        }
        $forms = $this->createPluginManagerMock(['Jobs/AdminCategories' => $form], $sm);

        $repository = $this->getMockBuilder(AbstractRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOneBy', 'store'])
            ->getMockForAbstractClass();

        if ($rootExists) {
            $professions = new Category('professions');
            $industries = new Category('industries');
            $types = new Category('Employment Types', 'employmentTypes');
            $repository->expects($this->exactly(3))->method('findOneBy')
                ->withConsecutive(
                    [['value' => 'professions']],
                    [['value' => 'employmentTypes']],
                    [['value' => 'industries']]
                )->will($this->onConsecutiveCalls(
                    $professions,
                    $industries,
                    $types
                                 ));
        } else {
            $repository->expects($this->exactly(3))->method('findOneBy')
                       ->withConsecutive(
                           [['value' => 'professions']],
                           [['value' => 'employmentTypes']],
                           [['value' => 'industries']]
                )->willReturn(null);
        }

        $repositories = $this->getMockBuilder(RepositoryService::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'store'])
            ->getMock();
        $repositories->expects($this->once())->method('get')->with('Jobs/Category')->willReturn($repository);

        if (!$rootExists) {
            $repositories->expects($this->exactly(3))->method('store')
                ->with($this->isInstanceOf(Category::class));
        } elseif ($postRequest) {
            $repositories->expects($this->exactly(1))->method('store')
                         ->with($this->isInstanceOf(Category::class));
        }





        $services = $this->createServiceManagerMock([
                'forms' => $forms,
                'repositories' => $repositories,
                'ViewHelperManager' => $viewHelpers,
            ]);

        return $services;
    }

    public function testNonPostRequestWithExistantRoots()
    {
        $services = $this->getConfiguredServiceManager();
        $this->target->initContainer($services);

        $model = $this->target->indexAction();

        $this->assertSame($services->get('forms')->get('Jobs/AdminCategories'), $model->getVariable('form'));
        $this->assertEquals('jobs/admin/categories', $model->getTemplate());
    }

    public function testNonPostRequestWithNonExistantRoots()
    {
        $services = $this->getConfiguredServiceManager(false);
        $this->target->initContainer($services);
        $model = $this->target->indexAction();
        $this->assertSame($services->get('forms')->get('Jobs/AdminCategories'), $model->getVariable('form'));
        $this->assertEquals('jobs/admin/categories', $model->getTemplate());
    }

    public function testPostRequest()
    {
        $services = $this->getConfiguredServiceManager(true, true);
        $this->target->initContainer($services);
        $this->target->getRequest()->setMethod('POST')->getQuery()->set('form', 'testFormId');

        $model = $this->target->indexAction();

        $this->assertInstanceOf(JsonModel::class, $model);
    }
}
