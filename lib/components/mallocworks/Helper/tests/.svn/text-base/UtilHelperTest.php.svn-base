<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\Helper\HelperFactory;

class UtilHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testBasic(){
        $h = HelperFactory::summon();

        $this->assertEquals(array(
            'generateId', 'generateHash', 'camelize', 'underscore', 'curl', 'serialize', 'unserialize', 'generateUuid'
        ), $h->getHelperNames('util'));

        $this->assertEquals('a:1:{s:1:"a";s:1:"A";}', $h->util_serialize(array('a'=>'A')));

        $this->assertEquals(array('a'=>'A'), $h->util_unserialize('a:1:{s:1:"a";s:1:"A";}'));
    }
}