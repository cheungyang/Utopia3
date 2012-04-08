<?php
require_once 'lib/components/mallocworks/Loader/Autoload.php';

use Utopia\Projects\CtrViewer\Controllers\RouteConfigurationController;
use Utopia\Components\ClassDispatcher\ClassDispatcher;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Autoload;

class RouteConfigurationControllerTest extends \PHPUnit_Framework_TestCase
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

        $this->url = 'ctr_viewer/module/route_selector';
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function checkAcceptance(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'GET');
        $this->assertEquals('RouteConfigurationController', $wf->getController());
    }

    public function testGET(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'GET');
        $c = $wf->getController();
        $this->assertEquals(ComponentRoot::OKAY, $c->GET());
        $this->assertTrue(in_array('module.route.selector', $c->getResultSet('routes')));
    }

//    public function testPOST(){}
//    public function testPUT(){}
//    public function testDELETE(){}

    public function testRenderGET(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'GET');
        $rtn = $wf->render();
        $expected = '<option name="module.route.selector">module.route.selector</option>';

        $this->assertTrue(false !== strpos($rtn, $expected));
    }

//    public function testRenderPOST(){}
//    public function testRenderPUT(){}
//    public function testRenderDELETE(){}
}