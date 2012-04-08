<?php
namespace Utopia\Component\DataAccess;

require_once dirname(__FILE__).'/../../Loader/Autoload.php';
require_once dirname(__FILE__).'/MysqliDataSourceTest.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\DataAccess\DataAccess;

class DataAccessTest extends MysqliDataSourceTest
{
    protected $_m;
    const TABLE = 'phpunit';

    protected function setUp() {
        Autoload::summon();
        $this->_m = DataAccess::summon('mysql');
    }

    protected function tearDown() {
        return parent::tearDown();
    }

    public function testInsert() {
        return parent::testInsert();
    }

    public function testUpdate() {
        return parent::testUpdate();
    }

    public function testDelete() {
        return parent::testDelete();
    }

    public function testTransaction() {
        return parent::testTransaction();
    }
}