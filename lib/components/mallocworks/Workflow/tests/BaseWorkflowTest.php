<?php
namespace Utopia\Component\Workflow;

use Utopia\Projects\Common\Controllers\HelloController;
use Utopia\Components\Workflow\IWorkflow;
use Utopia\Components\Controller\IController;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Autoload;
use Utopia\Components\Workflow\BaseWorkflow;
use Utopia\Components\Workflow\ErrorWorkflow;

require_once dirname(__FILE__).'/../../Loader/Autoload.php';

class BaseWorkflowTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function testGetController() {
        $controller = new HelloController("test/phpunit", "GET");
        $wf = ErrorWorkflow::summon()
            ->setController($controller);

        $this->assertEquals('Utopia\Projects\Common\Controllers\HelloController', get_class($wf->getController()));
    }
}