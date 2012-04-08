<?php
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
require_once dirname(__FILE__).'/../../Loader/Autoload.php';

use Utopia\Components\Loader\Autoload;
use Utopia\Components\Logger\Logger;

class LogTest extends PHPUnit_Framework_TestCase
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

    public function testNoLogs() {
        Logger::summon()->unsummon();
        ConfigurationBundle::summon()->setDeltaFile(dirname(__FILE__).'/delta_print_1.yml');
        $logger = Logger::summon();
        ob_start();
        $logger->log("error string");
        $logger->log("error string2", ComponentRoot::LEVEL_ERROR);
        $logger->log("warning string", ComponentRoot::LEVEL_WARNING);
        $logger->log("notice string", ComponentRoot::LEVEL_NOTICE);
        $logger->log("debug string", ComponentRoot::LEVEL_DEBUG);
        $logger->log("trival string", ComponentRoot::LEVEL_TRIVAL);
        $str = ob_get_clean();
        $this->assertEquals("", $str);
    }

    public function testPrintNormal() {
        Logger::summon()->unsummon();
        ConfigurationBundle::summon()->setDeltaFile(dirname(__FILE__).'/delta_print_2.yml');
        $logger = Logger::summon();
        ob_start();
        $logger->log("hello");
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "ERROR") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_ERROR);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "ERROR") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_WARNING);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "WARNING") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_NOTICE);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "NOTICE") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_DEBUG);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "DEBUG") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_TRIVAL);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "TRIVAL") &&  false !== strpos($str, "hello"));
    }

    public function testPrintColor(){
        Logger::summon()->unsummon();
        ConfigurationBundle::summon()->setDeltaFile(dirname(__FILE__).'/delta_print_3.yml');
        $logger = Logger::summon();
        ob_start();
        $logger->log("hello");
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "ERROR") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_ERROR);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "ERROR") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_WARNING);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "WARNING") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_NOTICE);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "NOTICE") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_DEBUG);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "DEBUG") &&  false !== strpos($str, "hello"));

        ob_start();
        $logger->log("hello", ComponentRoot::LEVEL_TRIVAL);
        $str = ob_get_clean();
        $this->assertTrue(false !== strpos($str, "TRIVAL") &&  false !== strpos($str, "hello"));
    }

    public function testResponseNormal() {
        Logger::summon()->unsummon();
        ConfigurationBundle::summon()->setDeltaFile(dirname(__FILE__).'/delta_response_1.yml');
        $logger = Logger::summon();
        $logger->log("hello");
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[0], "ERROR") &&  false !== strpos($str[0], "hello"));

        $logger->log("hello", ComponentRoot::LEVEL_ERROR);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[1], "ERROR") &&  false !== strpos($str[1], "hello"));

        $logger->log("hello", ComponentRoot::LEVEL_WARNING);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[2], "WARNING") &&  false !== strpos($str[2], "hello"));

        $logger->log("hello", ComponentRoot::LEVEL_NOTICE);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[3], "NOTICE") &&  false !== strpos($str[3], "hello"));

        $logger->log("hello", ComponentRoot::LEVEL_DEBUG);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[4], "DEBUG") &&  false !== strpos($str[4], "hello"));

        $logger->log("hello", ComponentRoot::LEVEL_TRIVAL);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[5], "TRIVAL") &&  false !== strpos($str[5], "hello"));
    }

    public function testResponseClassFiltering() {
        require_once dirname(__FILE__).'/FakeObject.php';
        $fake = new FakeObject();

        Logger::summon()->unsummon();
        ConfigurationBundle::summon()->setDeltaFile(dirname(__FILE__).'/delta_response_2.yml');
        $logger = Logger::summon();

        $logger->log("hello", ComponentRoot::LEVEL_ERROR);
        $str = $logger->getResponses();
        $this->assertEquals(array(), $str);

        $fake->write_log("hello", ComponentRoot::LEVEL_ERROR);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[0], "ERROR") &&  false !== strpos($str[0], "hello"));

        $fake->write_log("hello", ComponentRoot::LEVEL_ERROR);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[1], "ERROR") &&  false !== strpos($str[1], "hello"));

        $fake->write_log("hello", ComponentRoot::LEVEL_WARNING);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[2], "WARNING") &&  false !== strpos($str[2], "hello"));

        $fake->write_log("hello", ComponentRoot::LEVEL_NOTICE);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[3], "NOTICE") &&  false !== strpos($str[3], "hello"));

        $fake->write_log("hello", ComponentRoot::LEVEL_DEBUG);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[4], "DEBUG") &&  false !== strpos($str[4], "hello"));

        $fake->write_log("hello", ComponentRoot::LEVEL_TRIVAL);
        $str = $logger->getResponses();
        $this->assertTrue(false !== strpos($str[5], "TRIVAL") &&  false !== strpos($str[5], "hello"));
    }

    public function testErrorlogNormal() {
        Logger::summon()->unsummon();
        ConfigurationBundle::summon()->setDeltaFile(dirname(__FILE__).'/delta_errorlog_1.yml');
        $logger = Logger::summon();

        //$logger->log("hello", ComponentRoot::LEVEL_ERROR);
    }

    public function testFileNormal() {
        $filename = dirname(__FILE__).'/logger.phpunit.log';

        Logger::summon()->unsummon();
        if (file_exists($filename)) {
            unlink($filename);
        }
        ConfigurationBundle::summon()->setDeltaFile(dirname(__FILE__).'/delta_file_1.yml');
        $logger = Logger::summon();

        $logger->log("hello", ComponentRoot::LEVEL_ERROR);
        $str = file_get_contents($filename);
        $this->assertTrue(false !== strpos($str, "ERROR") &&  false !== strpos($str, "hello"));

        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}