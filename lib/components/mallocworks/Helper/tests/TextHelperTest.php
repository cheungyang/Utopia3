<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\Helper\HelperFactory;

class TextHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testBasic(){
        $h = HelperFactory::summon();

        $this->assertEquals(array(
            'getPureWords', 'tokenize', 'getStrip', 'stripHTML'
        ), $h->getHelperNames('text'));
    }
}