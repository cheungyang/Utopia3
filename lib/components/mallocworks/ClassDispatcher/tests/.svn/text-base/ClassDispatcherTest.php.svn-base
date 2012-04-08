<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Autoload;
use Utopia\Components\ClassDispatcher\ClassDispatcher;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

class ClassDispatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
        ConfigurationBundle::summon()
          ->setDimensionFile('conf/dimensions.yml')
          ->setTargetDimensions(array(
              'property'=>'common',
              'environment'=>'dev'))
          ->setDeltaFile(dirname(__FILE__).'/delta_classdispatcher_1.yml');

        $this->_c = ClassDispatcher::summon();

    }

    protected function tearDown() {
    }

    public function testGetRoutes(){
        $routes = $this->_c->getRouteNames();
        $this->assertEquals(array('core.hello','error.catch_all'), $routes);

        $route_info = $this->_c->getRoute('core.hello', 'description');
        $this->assertEquals('Hello Controller', $route_info);

        $route_info = $this->_c->getRoute('core.hello', 'required>name>type');
        $this->assertEquals('\w', $route_info);
    }

    public function testGetRouteByController(){
        $this->_c = ClassDispatcher::summon();
        $route_info = $this->_c->getRouteByController('Utopia\Core\Controller\HelloController', 'description');
        $this->assertEquals('Hello Controller', $route_info);

        $route_info = $this->_c->getRouteByController('Utopia\Core\Controller\HelloController', 'required>name>type');
        $this->assertEquals('\w', $route_info);

        $route_info = $this->_c->getRouteByController('Utopia\Core\Controller\ErrorController');
        $this->assertEquals(false, $route_info);
    }

    public function testCreateRegex(){
        $regex = $this->_c->getUrlRegex('core.hello');
        $expected = '/^hello\/(?P<name>[\w^\/]+?)\/(?P<myid>[0-9^\/]+?)(\/(?P<extra>.*))*?(.(?P<ext>(htm|html|php|json|css|js)))?$/';
        $this->assertEquals($expected, $regex);
    }

    public function testAcceptUrl(){
        $url = 'hello/ycheung/1234';
        $this->assertTrue($this->_c->acceptUrl('core.hello', $url, 'GET'));

        //error case
        $url = 'hello/error_string';
        $this->assertFalse($this->_c->acceptUrl('core.hello', $url, 'GET'));

        //add extension arg case
        $url = 'hello/ycheung/1234.html';
        $this->assertTrue($this->_c->acceptUrl('core.hello', $url, 'GET'));

        //add extension and extra case
        $url = 'hello/ycheung/1234/extra1/extra2.html';
        $this->assertTrue($this->_c->acceptUrl('core.hello', $url, 'GET'));
    }

    public function testGetArgument(){
        $this->_c = ClassDispatcher::summon();
        $url = 'hello/ycheung/1234';
        $this->_c->acceptUrl('core.hello', $url, 'GET');
        $this->assertEquals(array('myid'=>1234, 'name'=>'ycheung'), $this->_c->getArguments($url));

        //error case
        $url = 'hello/error_string';
        $this->_c->acceptUrl('core.hello', $url, 'GET');
        $this->assertEquals(array(), $this->_c->getArguments($url));

        //extra case
        $url = 'hello/ycheung/1234/extra1/extra2';
        $this->_c->acceptUrl('core.hello', $url, 'GET');
        $this->assertEquals(array('_extra'=>'extra1/extra2', 'myid'=>1234, 'name'=>'ycheung'), $this->_c->getArguments($url));

        $this->markTestIncomplete("the extra part is not parsed correctly in this stage");
        
        //add extension and extra case
        $url = 'hello/ycheung/1234/extra1/extra2.html';
        $this->assertEquals(array('_extra'=>'extra1/extra2', 'name'=>'ycheung', 'myid'=>1234), $this->_c->getArguments($url));
    }

    public function testDispatch(){
        $url = 'hello/ycheung/1234';
        $this->_c->dispatch($url, 'GET');
        $this->assertEquals('Utopia\Core\Controller\HelloController', $this->_c->getControllerClass($url));
        $this->assertEquals('Utopia\Core\Workflow\ApiWorkflow', $this->_c->getWorkflowClass($url));
        $this->assertEquals(array('myid'=>1234, 'name'=>'ycheung'), $this->_c->getArguments($url));

        $url = 'hello/error_string';
        $this->_c->dispatch('hello/error_string', 'GET');
        $this->assertEquals(false, $this->_c->getControllerClass($url));
        $this->assertEquals('Utopia\Core\Workflow\ErrorWorkflow', $this->_c->getWorkflowClass($url));
        $this->assertEquals(array(), $this->_c->getArguments($url));


    }
}