<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\DataModel\DataSchema;

class SchemaTest extends PHPUnit_Framework_TestCase
{
    protected $_schema;

    protected function setUp() {
        Autoload::summon();
        $this->_schema = new DataSchema(dirname(__FILE__).'/schema.phpunit.yml');
    }

    protected function tearDown() {
    }

    public function testGetSchema() {
        $f = $this->_schema->getSchema('user');
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
            ),
            array_keys($f['fields'])
        );
        $this->assertEquals(
            array(),
            array_keys($f['relationships'])
        );
    }

    public function testGetTableName(){
        $this->assertEquals('user', $this->_schema->getTableName('user'));
    }

    public function testGetEntityNames(){
        $this->assertEquals(array('user','article','category'), $this->_schema->getEntityNames());
    }

    public function testIsXX(){
        $this->assertEquals(false, $this->_schema->is_loc('category'));
        $this->assertEquals(true, $this->_schema->is_seq('category'));
        $this->assertEquals(true, $this->_schema->is_loc('article'));
        $this->assertEquals(false, $this->_schema->is_seq('article'));
    }
}