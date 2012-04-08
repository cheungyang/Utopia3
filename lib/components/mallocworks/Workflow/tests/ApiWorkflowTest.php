<?php
//mock ClassDispatcher class
namespace Utopia\Components\ClassDispatcher;

use Utopia\Projects\Common\Controllers\DumpController;
use Utopia\Components\Workflow\IWorkflow;
use Utopia\Components\Controller\IController;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Autoload;
use Utopia\Components\Workflow\BaseWorkflow;
use Utopia\Components\Workflow\ApiWorkflow;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

require_once dirname(__FILE__).'/../../Loader/Autoload.php';


class ApiWorkflowTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    
    protected function setUp() {
        Autoload::summon();
        
/*        $this->dispatcher = $this->getMock("ClassDispatcher");
        $this->dispatcher->expects($this->any())
             ->method("getArguments")
             ->will($this->returnValue(array('name'=>'ycheung', 'myid'=>'1234')));

        $callback = function() use(&$ref){
            $args = func_get_args();
            return ApiWorkflow::summon()
                ->setController(new DumpController($args[0], $args[1]));            
        };
        $this->dispatcher->expects($this->any())
             ->method("dispatch")
             ->will($this->returnCallback($callback));
*/
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function testRender() {
        $url = 'tests/phpunit';
        $method = 'GET';

        $wf = ApiWorkflow::summon()
            ->setController(new DumpController($url, $method));
        $json = $wf->render(array(
        	'format'=>'json',
            'GET'   =>array('param_array'=>array('start'=>0, 'count'=>10))
        ));

        $rtn_array = json_decode($json, true);
        $this->assertEquals(array('results', 'inputs', 'errors', 'elapsed'), array_keys($rtn_array));
        $this->assertEquals(array('args', 'params', 'url'), array_keys($rtn_array['inputs']));
        //$this->assertEquals(array('name'=>'ycheung', 'myid'=>'1234'), $rtn_array['inputs']['args']);
        $this->assertEquals(array('param_array'=>array('start'=>0, 'count'=>10)), $rtn_array['inputs']['params']);
        $this->assertEquals("tests/phpunit", $rtn_array['inputs']['url']);

    }
}