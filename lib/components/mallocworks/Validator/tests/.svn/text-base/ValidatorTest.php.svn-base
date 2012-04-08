<?php
use Utopia\Components\Core\ComponentRoot;
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\Validator\ValidationObject;
use Utopia\Components\Validator\Validator;

class ValidatorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testReq(){
        $obj = new ValidationObject();
        $obj->addArg('name');
        $v = Validator::summon();
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array()));

        $obj->setReq('name', true);
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array()));

        $obj->setType('name', 'array')->addChildren('name', array('first'=>array(), 'last'=>array()));
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>array())));

        $obj->setReq('name>first', true)->setReq('name>last', true);
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array()));
    }

    public function testDef(){
        $obj = new ValidationObject();
        $obj->addArg('name', 'string', false, 'abc');
        $v = Validator::summon();
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array()));
        $this->assertEquals('abc', $v->getFilteredValue('name'));

        $obj->setType('name', 'array')->addChildren('name',
            array(
            	'first'=>array('def'=>'alva'),
        		'last' =>array('def'=>'cheung')
        ));
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>array())));
        $this->assertEquals('alva', $v->getFilteredValue('name>first'));
    }

    public function testType(){
        $obj = new ValidationObject();
        $v = Validator::summon();
        //arguments
        $obj->addArg('name')->setType('name', 'string');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>'abc')));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>123)));

        $obj->setType('name', 'array');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>array(1,2,3))));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>123)));

        $obj->setType('name', 'int');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>123)));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>'abc')));

        $obj->setType('name', 'float');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>123.123)));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>'abc')));

        $obj->setType('name', 'bool');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>true)));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>'123')));

        //children
        $obj->setType('name', 'array')
            ->addChild('name', 'first')->setType('name>first', 'string');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>array('first'=>'abc'))));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>array('first'=>123))));

        $obj->setType('name>first', 'array');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>array('first'=>array(1,2,3)))));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>array('first'=>'abc'))));

        $obj->setType('name>first', 'int');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>array('first'=>123))));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>array('first'=>'abc'))));

        $obj->setType('name>first', 'float');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>array('first'=>123.123))));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>array('first'=>'abc'))));

        $obj->setType('name>first', 'bool');
        $this->assertEquals(ComponentRoot::OKAY, $v->validate($obj, array('name'=>array('first'=>true))));
        $this->assertEquals(ComponentRoot::ERROR, $v->validate($obj, array('name'=>array('first'=>'abc'))));
    }

    public function testFilter(){
    }
}