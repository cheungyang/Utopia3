<?php
//mock ClassDispatcher class
namespace Utopia\Components\ClassDispatcher;

require_once 'lib/components/mallocworks/Loader/Autoload.php';

use Utopia\Components\Workflow\BaseWorkflow;
use Utopia\Components\Workflow\ApiWorkflow;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Loader\Autoload;
use Utopia\Projects\Common\Controllers\DumpController;
use Utopia\Projects\Common\Controllers\HelloController;

class HelloControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $_m;
    const TABLE = 'phpunit';
    

    protected function setUp() {
        Autoload::summon();
        ConfigurationBundle::summon()
          ->setDimensionFile('conf/dimensions.yml')
          ->setTargetDimensions(array(
              'property'=>'common',
              'environment'=>'dev'))
          ->setDeltaFile('apps/common/deltas/delta.common.yml');

        $url = 'tests/phpunit';
        ClassDispatcher::summon()
            ->setArguments($url, array('name'=>'ycheung', 'myid'=>'1234'));
          
        //using HelloController as BaseController is an abstract class        
        $this->_c = new HelloController($url, 'GET');
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function testGetTemplate() {
        $template = $this->_c->getTemplate(false);
        $this->assertEquals('test1', $template);
    }

    public function testGetCss() {
        $css = $this->_c->getCss();
        $this->assertEquals(array('test1_css'), $css);
    }

    public function testGetJs() {
        $js = $this->_c->getJs();
        $this->assertEquals(array('test1_js'), $js);
    }

    public function testGet() {
        $this->_c->GET();
        $data = $this->_c->getResultSet();
        $this->assertEquals('hello world!', $data['greetings']);
    }

    public function testGetApiNames() {
        $names = $this->_c->getApiNames();
        $this->assertEquals(array('hello.shout'), $names);
    }

    public function testGetApi() {
        $wf = $this->_c->getApi('hello.shout');
        $this->assertTrue($wf instanceof BaseWorkflow);
        $wf = $this->_c->getApi('hello.shout2');
        $this->assertFalse($wf instanceof BaseWorkflow);
    }

    public function testGetModuleNames() {
        $names = $this->_c->getModuleNames();
        $this->assertEquals(array('hello.header'), $names);
    }

    public function testGetModule() {
        $wf = $this->_c->getModule('hello.header');
        $this->assertTrue($wf instanceof BaseWorkflow);
        $wf = $this->_c->getModule('hello.header2');
        $this->assertFalse($wf instanceof BaseWorkflow);
    }

}