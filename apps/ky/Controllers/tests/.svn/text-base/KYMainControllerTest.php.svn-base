<?php
require_once 'lib/components/mallocworks/Loader/Autoload.php';

use Utopia\Components\ClassDispatcher\ClassDispatcher;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Autoload;

class KYMainControllerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        $autoload = Autoload::summon();
        $bundle = ConfigurationBundle::summon();

        $bundle
            ->setDimensionFile('conf/dimensions.yml')
            ->setTargetDimensions(array(
              'property'=>'ky',
              'environment'=>'phpunit'))
            ->setDeltaFile('apps/ky/deltas/delta.ky.yml');
        $autoload->includeNamespaces($bundle->{'autoload.namespaces'});

        $this->url = 'ky/modules/main';
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function checkAcceptance(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'GET');
        $this->assertEquals('KYMain', $wf->getController());
    }

    public function testGET(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'GET');
        $c = $wf->getController();
        $this->assertEquals(ComponentRoot::OKAY, $c->GET());

        //print_r($c->getResultSet());
        $this->assertEquals(array(), $c->getResultSet());
    }

//    public function testPOST(){}
//    public function testPUT(){}
//    public function testDELETE(){}

    public function testRenderGET(){
        $wf = ClassDispatcher::summon()->dispatch($this->url, 'GET');
        $rtn = $wf->render();

        //print_r($rtn);
        $expected = '';
        $this->assertTrue(false !== strpos($rtn, $expected));
    }

//    public function testRenderPOST(){}
//    public function testRenderPUT(){}
//    public function testRenderDELETE(){}
}