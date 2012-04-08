<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\Validator\ValidationObject;
use Utopia\Components\DataModel\DataSchema;

class ValidationObjectTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testAddArg(){
        $v = new ValidationObject();
        $v->addArg("name");
        $this->assertEquals(array('name'), $v->getArgs());

        $v->addArgs(array('user'=>'', 'password'=>''));
        $this->assertEquals(array('name', 'user', 'password'), $v->getArgs());

        $v->deleteArg("name");
        $this->assertEquals(array('user', 'password'), $v->getArgs());
    }

    public function testAddChild(){
        $v = new ValidationObject();
        $v->addArg("name", 'array')
          ->addChild("name", "first");
        $this->assertEquals(array('first'), $v->getChildren('name'));

        $v->addChildren('name', array('middle'=>'', 'last'=>''));
        $this->assertEquals(array('first', 'middle', 'last'), $v->getChildren("name"));

        $v->deleteArg("name>first");
        $this->assertEquals(array('middle', 'last'), $v->getChildren("name"));

    }

    public function testGettersAndSetters(){
        $v = new ValidationObject();
        $v->addArg('name')
          ->setType('name', 'integer')
          ->setDef('name', '999')
          ->setReq('name', true)
          ->setFilter('name', 'email');

        $this->assertEquals('integer', $v->getType('name'));
        $this->assertEquals(999, $v->getDef('name'));
        $this->assertTrue($v->getReq('name'));
        $this->assertEquals('email', $v->getFilter('name'));
    }

    public function testGetValidationObjectFromSchema(){
        $s = new DataSchema(dirname(__FILE__).'/schema.phpunit.yml');
        $v = new ValidationObject();
        $v->fromSchema($s, 'user');
        $this->assertEquals(
            array (
              0 => 'username',
              1 => 'email',
              2 => 'password',
              3 => 'id',
              4 => 'name',
              5 => 'created_at',
              6 => 'modified_at',
              7 => 'is_active',
              8 => 'is_close',
              9 => 'is_block',
              10 => 'is_delete',
              11 => 'flow',
            ), $v->getArgs()
        );
    }

    public function testToHtmlTags(){
        $v = new ValidationObject();
        $v->addArg('text', 'text', true, 'hello')
          ->addArg('int', 'integer', true, 999)
          ->addArg('bool', 'bool', true, true)
          ->addArg('datetime', 'datetime', true, "2011-06-03")
          ->addArg('unknown', 'xxx', true, "")
          ->addArg('select', array('Y','N'), true, "")
          ->addArg('array', 'array', true, array())
          ->addChild("array", "atext", 'text', true, 'children');
        $tags = $v->toHtmlTags();

        #different types
        $this->assertEquals('<input type="text" id="text" name="text" value="hello"/>', $tags['text']);
        $this->assertEquals('<input type="text" id="int" name="int" value="999"/>', $tags['int']);
        $this->assertEquals("<select id=\"bool\" name=\"bool\">\n\t<option value=\"1\">TRUE</option>\n\t<option value=\"0\">FALSE</option>\n</select>", $tags['bool']);
        $this->assertEquals('<input type="text" id="datetime" name="datetime" value="2011-06-03"/>', $tags['datetime']);
        $this->assertEquals('<input type="text" id="unknown" name="unknown" value=""/>', $tags['unknown']);
        $this->assertEquals("<select id=\"select\" name=\"select\">\n\t<option value=\"Y\">Y</option>\n\t<option value=\"N\">N</option>\n</select>", $tags['select']);

        #children
        $this->assertTrue(is_array($tags['array']));
        $this->assertEquals('<input type="text" id="array_atext" name="array[atext]" value="children"/>', $tags['array']['atext']);

        #prefix
        $tags = $v->toHtmlTags('hello');
        $this->assertEquals('<input type="text" id="hello_text" name="hello[text]" value="hello"/>', $tags['text']);
    }
}