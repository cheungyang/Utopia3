<?php
use Utopia\Components\Workflow\BaseWorkflow;
require_once 'lib/components/mallocworks/Loader/Autoload.php';

use Utopia\Projects\CtrViewer\Controllers\RouteExecutionController;
use Utopia\Components\ClassDispatcher\ClassDispatcher;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Autoload;

class RouteExecutionControllerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        $autoload = Autoload::summon();
        $bundle = ConfigurationBundle::summon();

        $bundle
            ->setDimensionFile('conf/dimensions.yml')
            ->setTargetDimensions(array(
              'property'=>'ctr_viewer',
              'environment'=>'dev'))
            ->setDeltaFile('apps/ctr_viewer/deltas/delta.ctr_viewer.yml');
        $autoload->includeNamespaces($bundle->{'autoload.namespaces'});

        $this->url = 'ctr_viewer/module/route_execution';
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function checkAcceptance(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'POST');
        $this->assertEquals('RouteExecutionController', $wf->getController());
    }

//    public function testGET(){}

    public function testPOST(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'POST');
        $this->assertTrue($wf instanceof BaseWorkflow);

        $c = $wf->getController();
        $c->setParams(array(
            'url' => 'ctr_viewer/module/route_selector',
            'method' => 'GET',
            'param_str' => ''
        ));
        $c->validateParams();
        $this->assertEquals(ComponentRoot::OKAY, $c->POST());

        $resultset = $c->getResultSet();
        $expected = 'select';

        //print_r($rtn);
        $this->assertTrue(false !== strpos($resultset['render'], $expected));
    }

//    public function testPUT(){}
//    public function testDELETE(){}

//    public function testRenderGET(){}

    public function testRenderPOST(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'POST');
        $rtn = $wf->render(array('POST'=>array(
            'url' => 'ctr_viewer/module/route_selector',
            'method' => 'GET',
            'param_str' => ''
        )));
        $expected = 'select';

        //print_r($rtn);
        $this->assertTrue(false !== strpos($rtn, $expected));
    }

//    public function testRenderPUT(){}
//    public function testRenderDELETE(){}
}