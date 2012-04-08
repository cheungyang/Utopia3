<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\DataModel\MigrationDb;
use Utopia\Components\DataModel\DataSchema;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

class MigrationDbTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
        ConfigurationBundle::summon()
          ->setDimensionFile('conf/dimensions.yml')
          ->setTargetDimensions(array(
              'property'=>'common',
              'environment'=>'dev'));
    }

    protected function tearDown() {
    }

    public function testMigration() {
        ConfigurationBundle::summon()->setDeltaFile(dirname(__FILE__).'/delta_migrationdb_1.yml');
        $db = Migrationdb::summon();
        $schema = new DataSchema(dirname(__FILE__).'/schema.phpunit.yml');

        $queries1 = $db->getMigrationQuery($schema);
        $queries2 = $db->getMigrationQuery($schema, false);

        $this->assertTrue(is_array($queries1));
        $this->assertTrue(is_array($queries2));
    }
}