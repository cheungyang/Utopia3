<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\DataAccess\MysqliQuery;

class MysqliQueryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testDelete() {
        $this->assertEquals('DELETE FROM article WHERE ( (id=?) )', MysqliQuery::delete('article')->where('id',10)->getSql() );
        $this->assertEquals('DELETE FROM article WHERE ( (id=?) ) ORDER BY id ASC LIMIT 1', MysqliQuery::delete('article')->where('id',10)->order('id','ASC')->limit(1)->getSql() );
    }

    public function testUpdate() {
        $q = MysqliQuery::update('article');
        $this->assertEquals('UPDATE article SET `a`=? WHERE ( (id=?) )', $q->set('a', 'A')->where('id',10)->getSql() );
        $this->assertEquals(array('A',10), $q->getParams());
        $q = MysqliQuery::update('article');
        $this->assertEquals('UPDATE article SET `a`=? , `b`=? WHERE ( (id=?) )', $q->set(array('a'=>'A','b'=>'B'))->where('id',10)->getSql() );
        $this->assertEquals(array('A','B',10), $q->getParams());

        //with order/limit
        $q = MysqliQuery::update('article');
        $this->assertEquals('UPDATE article SET `a`=? WHERE ( (id=?) ) LIMIT 1', $q->set(array('a'=>10))->where('id',10)->limit(1)->getSql() );
        $this->assertEquals(array(10,10), $q->getParams());
        $q = MysqliQuery::update('article');
        $this->assertEquals('UPDATE article SET `a`=? WHERE ( (id=?) ) ORDER BY id ASC LIMIT 1', $q->set(array('a'=>10))->where('id',10)->limit(1)->order('id','ASC')->getSql() );
        $this->assertEquals(array(10,10), $q->getParams());
    }

    public function testInsert() {
        $q = MysqliQuery::insert('article');
        $this->assertEquals('INSERT INTO article (`a`, `b`) VALUES(?, ?)', $q->values(array('a'=>'A','b'=>'B'))->getSql() );
        $this->assertEquals(array('A','B'), $q->getParams());
        $q = MysqliQuery::insert('article');
        $this->assertEquals('INSERT INTO article (`a`, `b`) VALUES(?, ?) ON DUPLICATE KEY UPDATE `a`=?', $q->values(array('a'=>'A','b'=>'B'))->duplicatekey('a')->getSql() );
        $this->assertEquals(array('A','B','A'), $q->getParams());
        $q = MysqliQuery::insert('article');
        $this->assertEquals('INSERT INTO article (`a`, `b`) VALUES(?, ?) ON DUPLICATE KEY UPDATE `a`=? , `b`=?', $q->values(array('a'=>'A','b'=>'B'))->duplicatekey(array('a','b'))->getSql() );
        $this->assertEquals(array('A','B','A','B'), $q->getParams());
    }

    public function testSelect() {
        //select
        $this->assertEquals('SELECT * FROM a', MysqliQuery::select('*')->from('a')->getSql() );
        $this->assertEquals('SELECT a, b FROM a', MysqliQuery::select(array('a','b'))->from('a')->getSql() );
        $this->assertEquals('SELECT DISTINCT * FROM a', MysqliQuery::select('*')->from('a')->distinct(true)->getSql() );

        //from
        $this->assertEquals('SELECT * FROM a', MysqliQuery::select('*')->from('a')->getSql() );
        $this->assertEquals('SELECT * FROM a, b', MysqliQuery::select('*')->from(array('a','b'))->getSql() );
        $this->assertEquals('SELECT * FROM a LEFT JOIN b ON a.id=b.id', MysqliQuery::select('*')->from('a')->leftJoin('b','a.id=b.id')->getSql() );

        //where
        $q = MysqliQuery::select('*');
        $this->assertEquals('SELECT * FROM a WHERE ( (a=?) )', $q->from('a')->where('a','b')->getSql());
        $this->assertEquals(array('b'), $q->getParams());
        $q = MysqliQuery::select('*');
        $this->assertEquals('SELECT * FROM a WHERE ( (a=?) && (b=?) )', $q->from('a')->where(array('a'=>'A','b'=>'B'))->getSql());
        $this->assertEquals(array('A','B'), $q->getParams());
        $q = MysqliQuery::select('*');
        $this->assertEquals('SELECT * FROM a WHERE ( (a=?) ) || ( (b=?) )', $q->from('a')->where('a','A')->orWhere('b','B')->getSql());
        $this->assertEquals(array('A','B'), $q->getParams());
        $q = MysqliQuery::select('*');
        $this->assertEquals('SELECT * FROM a WHERE ( (a=?) ) || ( (b=?) && (c=?) )', $q->from('a')->where('a','A')->orWhere(array('b'=>'B','c'=>'C'))->getSql());
        $this->assertEquals(array('A','B','C'), $q->getParams());

        //group & having
        $this->assertEquals('SELECT *, COUNT(*) as __count FROM a GROUP BY id HAVING id=2', MysqliQuery::select('*')->from('a')->group('id')->having('id=2')->getSql() );

        //order, limit
        $this->assertEquals('SELECT * FROM a ORDER BY id ASC', MysqliQuery::select('*')->from('a')->order('id','asc')->getSql() );
        $this->assertEquals('SELECT * FROM a LIMIT 10', MysqliQuery::select('*')->from('a')->limit(10)->getSql() );

        //offset
        $this->assertEquals('SELECT * FROM a LIMIT 5,0', MysqliQuery::select('*')->from('a')->offset(5)->getSql() );
        $this->assertEquals('SELECT * FROM a LIMIT 5,10', MysqliQuery::select('*')->from('a')->offset(5)->limit(10)->getSql() );
        $this->assertEquals('SELECT * FROM a WHERE ( (id>?) ) ORDER BY id ASC', MysqliQuery::select('*')->from('a')->offset('id','20','ASC')->getSql() );
        $q = MysqliQuery::select('*');
        $this->assertEquals('SELECT * FROM a WHERE ( (a=?) && (id>?) ) ORDER BY id ASC', $q->from('a')->where('a','A')->offset('id','20','ASC')->getSql() );
        $this->assertEquals(array('A',20), $q->getParams());
        $q = MysqliQuery::select('*');
        $this->assertEquals('SELECT * FROM a WHERE ( (a=?) && (id>?) ) || ( (b=?) && (id>?) ) ORDER BY id ASC', $q->from('a')->where('a','A')->orWhere('b','B')->offset('id','20','ASC')->getSql() );
        $this->assertEquals(array('A',20,'B',20), $q->getParams());
    }
}