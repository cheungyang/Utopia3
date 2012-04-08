<?php
require_once dirname(__FILE__).'/../Autoload.php';

use Utopia\Components\Loader\Autoload;

class AutoloadTest extends PHPUnit_Framework_TestCase
{
    protected $autoload;

    protected function setUp() {
        //do this to escape from using ::summon()
        $this->autoload = new Autoload();
        $this->autoload->includeNamespace('Utopia\Components', 'lib/components/mallocworks');
    }

    protected function tearDown() {
        unset($this->autoload);
    }

    public function testPhp53(){
        //load TestObject for example
        $obj = null;
        try{
            $obj = new Utopia\Components\Loader\TestObject();
        } catch(\Exception $e){
        }
        $this->assertTrue($obj instanceof Utopia\components\Loader\TestObject);
    }

    public function testGetIncludedNamespaces(){
        $this->assertEquals(
        	array('Utopia\Components'),
        	$this->autoload->getIncludedNamespaces()
	    );
    }
}