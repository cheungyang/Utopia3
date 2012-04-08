<?php
require_once dirname(__FILE__).'/../Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\Loader\Loader;

class LoaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
    }

    protected function tearDown() {
    }

    public function testGetFilePath() {
        $filename = Loader::summon()->getFilePath('Loader');
        echo ">> $filename\n";
        $this->assertEquals(
            str_replace('\\','/',realpath(dirname(__FILE__).'/../Loader.php')),
            str_replace('\\','/',$filename));
    }

    public function testNamespace(){
        $classes = Loader::summon()->getNamespaceClasses('Utopia\Components\Loader', true);
        $this->assertTrue(in_array('Utopia\Components\Loader\Loader', $classes));
    }

    public function testGetNamespaceModtime(){
        $modtime = Loader::summon()->getNamespaceModtime('Utopia\Components\Loader', true);
        $this->assertTrue($modtime>0);
    }
}