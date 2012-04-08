<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\Console\Console;

class ConsoleTest extends PHPUnit_Framework_TestCase
{
    private $_sc;
    private $_autoload;

    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testBasic() {
        $console = new Console();
        $this->assertEquals('Utopia\Components\Console\Console', get_class($console));
    }
}