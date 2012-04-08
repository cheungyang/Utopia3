<?php
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\DataAccess\MysqliQuery;
use Utopia\Components\DataModel\DataModel;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

class DataModelTest extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        Autoload::summon();
        ConfigurationBundle::summon()
          ->setDimensionFile('conf/dimensions.yml')
          ->setTargetDimensions(array(
              'property'=>'common',
              'environment'=>'dev'))
          ->setDeltaFile(dirname(__FILE__).'/delta_datamodel_1.yml');
    }

    protected function tearDown() {
    }

    public function testInsert(){
        $dm = DataModel::summon('article');
        $input = array(
            'name' =>'phpunit test '.time(),
            'story'=>'long story short...'
        );
        $dm->insert($input);

        $this->assertEquals(200, $dm->getLatestStatus());
        $this->assertEquals($input['name'], $dm->getLatestData('0>name'));
        $this->assertEquals('insert', $dm->getLatestOperation('operation'));
    }

    public function testUpdate(){
        $dm = DataModel::summon('article');
        $input = array(
            'name' =>'phpunit test '.time(),
            'story'=>'long story short...'
        );
        $id = $dm->insert($input)->getLatestData('0>id');

        $update = array(
            'story'=>'i think this deserves a longer length...'
        );
        $rtn = $dm->update($id, $update);

        $this->assertEquals(200, $dm->getLatestStatus());
        $this->assertEquals($update['story'], $dm->getLatestData('0>story'));
        $this->assertEquals('update', $dm->getLatestOperation('operation'));
    }

    public function testDelete(){
        $dm = DataModel::summon('article');
        $input = array(
            'name' =>'phpunit test '.time(),
            'story'=>'long story short...'
        );
        $id = $dm->insert($input)->getLatestData('0>id');

        //mark delete
        $dm->delete($id);
        $this->assertEquals(1, $dm->fetchById($id)->getLatestData('0>is_delete'));

        //real delete
        $dm->delete($id, true);
        $this->assertEquals(array(), $dm->fetchById($id)->getLatestData());
    }

    public function testFetch(){
        $dm = DataModel::summon('article');
        $input = array(
            'name' =>'phpunit test '.time(),
            'story'=>'long story short...'
        );
        $id = $dm->insert($input)->getLatestData('0>id');

        //fetch by Query
        $q = MysqliQuery::select()->where(array('id'=>$id));
        $dm->fetch($q);
        $this->assertEquals(200, $dm->getLatestStatus());
        $this->assertEquals($input['story'], $dm->getLatestData('0>story'));

        //fetch by Query 2
        $q = MysqliQuery::select()->where(array('id'=>$id));
        $dm->fetchByQuery($q);
        $this->assertEquals(200, $dm->getLatestStatus());
        $this->assertEquals($input['story'], $dm->getLatestData('0>story'));

        //fetch by id
        $dm->fetch($id);
        $this->assertEquals(200, $dm->getLatestStatus());
        $this->assertEquals($input['story'], $dm->getLatestData('0>story'));

        //fetch by id 2
        $dm->fetchById($id);
        $this->assertEquals(200, $dm->getLatestStatus());
        $this->assertEquals($input['story'], $dm->getLatestData('0>story'));

        //fetch by name
        $dm->fetchByName($input['name']);
        $this->assertEquals('SELECT * FROM articles WHERE ( (name=?) )', $dm->getLatestOperation('inputs')->getSql());
        $this->assertEquals(200, $dm->getLatestStatus());
        $this->assertEquals($input['story'], $dm->getLatestData('0>story'));
    }
}