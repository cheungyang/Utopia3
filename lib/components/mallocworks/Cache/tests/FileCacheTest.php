<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Autoload;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Cache\FileCache;

class FileCacheTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
        ConfigurationBundle::summon()
          ->setDimensionFile('conf/dimensions.yml')
          ->setTargetDimensions(array(
              'property'=>'common',
              'environment'=>'phpunit'))
          ->setDeltaFile(dirname(__FILE__).'/delta_cache_1.yml');
    }

    protected function tearDown() {
    }

    public function testBasic(){
        $cache = FileCache::summon();
        $key = "phpunit-timestamp";
        $value = time();
        $cache->add($key, $value);
        $rtn = $cache->get($key);

        $this->assertEquals($cache->isEnabled(), 1);
        $this->assertEquals($value, $rtn);
    }

    public function testExists(){
        $cache = FileCache::summon();
        $key = "phpunit-timestamp";
        $value = time();
        $cache->delete($key);
        $this->assertEquals(false, $cache->exists($key));

        $cache->add($key, $value);
        $rtn = $cache->get($key);
        $this->assertEquals(true, $cache->exists($key));
    }
}