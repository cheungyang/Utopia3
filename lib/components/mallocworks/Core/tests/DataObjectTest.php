<?php
require_once dirname(__FILE__).'/../DataObject.php';

use Utopia\Components\Core\DataObject;

class DataObjectTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
    }

    protected function tearDown() {
    }

    public function testBasic() {
        $d = new DataObject();

        //getter, setters, and isset
        $d->set('a', 'a');
        $this->assertEquals('a', $d->get('a'));
        $this->assertEquals('a', $d->a);
        $this->assertTrue(isset($d->a));
        $d->set('b>1', 'b');
        $this->assertEquals('b', $d->get('b>1'));
        $this->assertEquals('b', $d->{'b>1'});
        $this->assertTrue(isset($d->{'b>1'}));
        $d->set('c', 'c');
        $this->assertEquals(false, $d->get('C'));
        $this->assertEquals(false, $d->C);
        $this->assertFalse(isset($d->C));
        $d->{'d>1'} = 'd';
        $this->assertEquals('d', $d->{'d>1'});
        $d->{'e>1>2>3>4>5>6>7>8>9'} = 10;
        $this->assertEquals(10, $d->{'e>1>2>3>4>5>6>7>8>9'});
        $this->assertTrue(isset($d->{'e>1>2>3>4'}));

        //del
        $d->set('f', 'f')->del('f');
        $this->assertFalse(isset($d->f));
        $d->set('g>1>2>3>4>5>6>7>8>9',10)->del('g>1>2>3>4>5');
        $this->assertFalse(isset($d->{'g>1>2>3>4>5>6'}));
        $this->assertFalse(isset($d->{'g>1>2>3>4>5'}));
        $this->assertTrue(isset($d->{'g>1>2>3>4'}));

        //insert
        $d->h = array(1,2,3,4,5);
        $this->assertEquals(10, $d->insert('h', 10, 3)->get('h>3'));
        $d->insert('i', 10, 3);
        $this->assertEquals(10, $d->get('i>0'));

        //push and pop
        $d->j = array(1,2);
        $this->assertEquals(2, $d->pop('j'));
        $this->assertEquals(1, $d->pop('j'));
        $this->assertEquals(false, $d->pop('j'));
        $d->push('j', 3)->push('j', 4)->push('j', 5);
        $this->assertEquals(5, $d->pop('j'));
        $this->assertEquals(4, $d->pop('j'));

        //shirt an unshift
        $d->k = array(1,2);
        $this->assertEquals(1, $d->shift('k'));
        $this->assertEquals(2, $d->shift('k'));
        $this->assertEquals(false, $d->shift('k'));
        $d->unshift('k', 3)->unshift('k', 4)->unshift('k', 5);
        $this->assertEquals(5, $d->shift('k'));
        $this->assertEquals(4, $d->shift('k'));

        //range functions
        $d->l = array('A'=>'aa', 'B'=>'bb', 'C'=>'cc', 'D'=>'dd');
        $this->assertEquals(array('bb','cc'), $d->getRangeValues('l', 1, 2));
        $this->assertEquals(array('bb','cc','dd'), $d->getRangeValues('l', 1, 100));
        $this->assertEquals(array('aa'), $d->getRangeValues('l', -100, 1));
        $this->assertEquals(array('B','C'), $d->getRangeKeys('l', 1, 2));
        $this->assertEquals(array('B','C','D'), $d->getRangeKeys('l', 1, 100));
        $this->assertEquals(array('A'), $d->getRangeKeys('l', -100, 1));
        $this->assertEquals(array('A'=>'aa','C'=>'cc', 'D'=>'dd'), $d->delRange('l', 1, 1)->get('l'));
        $this->assertEquals(array('A'=>'aa'), $d->delRange('l', 1)->get('l'));

        //iterate
        $d->m = array('A'=>'aa', 'B'=>'bb', 'C'=>'cc', 'D'=>'dd');
        $d->setPointer('m');
        $keys = array_keys($d->m);
        $values = array_values($d->m);
        $counter = 0;
        foreach($d as $key=>$val){
            $this->assertEquals($keys[$counter], $key);
            $this->assertEquals($values[$counter], $val);
            $counter++;
        }

        //symlinks
        $d->{'n>1>2>3>4>5>6>7>8>9'} = 10;
        $d->ln('n>1>2>3>4>5>6>7>8', 'longlink');
        $this->assertEquals(10, $d->pr('longlink>9', true));
        $d->delln('longlink');
        $this->assertEquals(false, $d->pr('longlink>9', true));

        //merge
        $d->p = array(1,2,3,4,5);
        $this->assertEquals(9, $d->merge('p', array(6,7,8,9))->get('p>8'));
    }
}