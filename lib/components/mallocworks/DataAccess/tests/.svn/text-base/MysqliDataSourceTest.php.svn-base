<?php
namespace Utopia\Component\DataAccess;

require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\DataAccess\MysqliDataSource;
use Utopia\Components\DataAccess\MysqliQuery;

class MysqliDataSourceTest extends \PHPUnit_Framework_TestCase
{
    protected $_m;
    const TABLE = 'phpunit';

    protected function setUp() {
        Autoload::summon();
        $this->_m = MysqliDataSource::summon('mysql');
    }

    protected function tearDown() {
        $q = MysqliQuery::select('count(*) as count')->from(self::TABLE);
        $rtn = $this->_m->fetch($q);
        $this->_m->delete(self::TABLE, array(), true, $rtn[0]['count']);
    }

    public function testInsert() {
        $inputs = array('name'=>time());
        $rtn = $this->_m->insert(self::TABLE, $inputs);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);
    }

    public function testUpdate() {
        $inputs = array('name'=>time());
        $rtn = $this->_m->insert(self::TABLE, $inputs);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);

        $criteria = array('id'=>$rtn[0]['id']);
        $inputs = array('name'=>time()."-updated");
        $rtn = $this->_m->update(self::TABLE, $criteria, $inputs);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);
    }

    public function testDelete() {
        $inputs = array('name'=>time());
        $rtn = $this->_m->insert(self::TABLE, $inputs);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);

        //flag delete
        $criteria = array('id'=>$rtn[0]['id']);
        $rtn = $this->_m->delete(self::TABLE, $criteria);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);
        $q = MysqliQuery::select()->from(self::TABLE)->where($inputs)->where('is_delete',1);
        $rtn = $this->_m->fetch($q);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);

        //real delete
        $rtn = $this->_m->delete(self::TABLE, $criteria, true);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);
        $q = MysqliQuery::select()->from(self::TABLE)->where($criteria);
        $rtn = $this->_m->fetch($q);
        $this->assertEquals(array(), $rtn);
    }

    public function testTransaction() {
        $inputs = array('name'=>time());
        $rtn = $this->_m->insert(self::TABLE, $inputs);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);

        $this->_m->begin()
          ->delete(self::TABLE, $inputs, true);
        $q = MysqliQuery::select()->from(self::TABLE)->where($inputs);
        $rtn = $this->_m->fetch($q);
        $this->assertEquals(array(), $rtn);

        $this->markTestIncomplete("rollback is not functioning correctly.");
        
        $this->_m->rollback();
        $q = MysqliQuery::select()->from(self::TABLE)->where($inputs);
        $rtn = $this->_m->fetch($q);
        $this->assertEquals($inputs['name'], $rtn[0]['name']);
    }
}