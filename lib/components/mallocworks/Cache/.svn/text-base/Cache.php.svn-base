<?php
namespace Utopia\Components\Cache;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Logger\Logger;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

class Cache extends ComponentRoot{

    static private $_instance = null;

    static public function isSingleton(){
        return true;
    }

    public static function summon($mixed=false){
        if (is_null(self::$_instance)) {
            $cb = ConfigurationBundle::summon();
            $class = $cb->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF)
                        ->get_value('cache.generic.engine_class');
            $instance = new $class();
            $instance->initialize($mixed);
            self::$_instance = $instance;
        }
        return self::$_instance;
    }

    public function initialize($mixed=false) {}

    /**
     * if engine_class = "Utopia\Components\Cache\FileCacahe", need array('cache.file.filena,e'=>'<filename>')
     *
     * @param string $engine_class
     * @param array $options
     */
    public static function summonNewInstance($engine_class, $options=array()) {

    }
}