<?php
use Utopia\Components\Workflow\WorkflowKeeper;

use Utopia\Projects\Common\Controllers\DumpController;
use Utopia\Components\Workflow\IWorkflow;
use Utopia\Components\Controller\IController;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Autoload;
use Utopia\Components\Workflow\BaseWorkflow;
use Utopia\Components\Workflow\ApiWorkflow;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

require_once dirname(__FILE__).'/../../Loader/Autoload.php';

class WorkflowKeeperTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function testAddWorkflow() {
        $url = 'tests/phpunit';
        $expected = 'GET_tests%2Fphpunit_40cd750bba9870f18aada2478b24840a';
        $method = 'GET';

        $controller = new DumpController('tests/phpunit', 'GET');
        $wf = ApiWorkflow::summon()->setController($controller);
        $keeper = WorkflowKeeper::summon();
        $this->assertEquals(array($expected), $keeper->addWorkflow($wf)->getWorkflowNames());

        $key = $keeper->getWorkflowKey($wf);
        $this->assertEquals($expected, $key);
        
        $wf2 = $keeper->getWorkflow($key);
        $this->assertEquals($wf, $wf2);
    }
}