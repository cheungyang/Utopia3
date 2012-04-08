<?php
namespace Utopia\Component\DataAccess;

use Utopia\Components\Core\ComponentRoot;

require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Projects\Common\Controllers\BaseController;
use Utopia\Projects\Common\Controllers\DumpController;

class BaseControllerTest extends \PHPUnit_Framework_TestCase
{
    protected $_m;
    const TABLE = 'phpunit';

    protected function setUp() {
        Autoload::summon();
        //using DumpController as BaseController is an abstract class
        $this->_c = new DumpController('tests/phpunit', 'GET');
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function testGetValidationObject() {
        $validator = $this->_c->getValidationObject('GET');
        $this->assertEquals(array('param_array'), $validator->getArgs());
    }

//    public function testValidate(){
//        $get['args'] = array('a'=>'a','b'=>'b');
//        $rtn = $this->_c->setInputs($get)->validateInputs('GET');
//        $this->assertEquals(ComponentRoot::OKAY, $rtn);
//        $this->assertEquals('a', $this->_c->getInputs('args>a'));
//        $this->assertEquals($get, $this->_c->getInputs());
//
//        $rtn = $this->_c->validateInputs('GET');
//        $this->assertEquals(ComponentRoot::OKAY, $rtn);
//        $this->assertEquals($get, $this->_c->getInputs());
//    }
}