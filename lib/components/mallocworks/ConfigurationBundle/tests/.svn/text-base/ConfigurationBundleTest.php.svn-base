<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

class ConfigurationBundleTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        $autoload = new Autoload();
        $autoload
            ->includeNamespace('Utopia\Components', 'lib/components/mallocworks')
            ->includeNamespace('Symfony\Components', 'lib/components/symfony');
    }

    protected function tearDown() {
    }

    public function testBasic() {
        $b = ConfigurationBundle::summon();
        $b->setMasterFile(dirname(__FILE__).'/master.ini');
        $this->assertEquals("master1.FOO", $b->{'component1.foo'});

        $b->setDimensionFile(dirname(__FILE__).'/dimensions.yml')
          ->setDeltaFile(dirname(__FILE__).'/delta2.ini')
          ->setTargetDimensions(array(
              'property'=>'common',
              'role'=>'api',
              'environment'=>'dev'));
        $this->assertEquals("delta2.FOO", $b->{'component1.foo'});

        $b->setDeltaFile(dirname(__FILE__).'/delta1.yml');
        $this->assertEquals("delta1.FOO", $b->{'component1.foo'});
    }
}