<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\Helper\HelperFactory;

class FileHelperTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testBasic(){
        $h = HelperFactory::summon();

        $this->assertEquals(array(
            'makeDirs', 'moveFiles', 'copyFiles', 'filterContents', 'deleteDirs', 'listFiles'
        ), $h->getHelperNames('file'));
    }
}