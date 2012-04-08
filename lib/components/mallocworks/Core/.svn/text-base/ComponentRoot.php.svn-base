<?php
namespace Utopia\Components\Core;

use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Core\IComponentRoot;

abstract class ComponentRoot implements IComponentRoot{

    const OKAY = 200;
    const DUPLICATED = 201;
    const NOTFOUND = 404;
    const NOAUTH = 401;
    const ERROR = -999;
    const ERROR_VALIDATION = -998;

    const LEVEL_ERROR = 99;
    const LEVEL_WARNING = 50;
    const LEVEL_NOTICE = 20;
    const LEVEL_DEBUG = 10;
    const LEVEL_TRIVAL = 0;

    const MASTER_CONF = 'master.ini';    //filename of the master config file

    //list of alive instances
    private static $_minions = array();
    //id of the current instance
    private $_minion_id = false;


    public function __destruct(){
        $this->unsummon();
    }

    public static function summon($mixed=false){
        $childclass = get_called_class();

        //singleton handling
        if ($childclass::isSingleton()) {
            if (!isset(self::$_minions[$childclass]) || empty(self::$_minions[$childclass])){
                $instance = new $childclass();
                $instance->initialize($mixed);
                if (!isset(self::$_minions[$childclass])){
                    self::$_minions[$childclass] = array();
                }
                self::$_minions[$childclass][] = $instance;
                //echo "> $childclass summoned\n";
            }
            return current(self::$_minions[$childclass]);
        }
        //new object handling
        else {
            $instance = new $childclass();
            $instance->initialize($mixed);
            if (!isset(self::$_minions[$childclass])){
                self::$_minions[$childclass] = array();
            }
            self::$_minions[$childclass][] = $instance;
            //echo "> $childclass summoned\n";
            return $instance;
        }
    }

    public static function is_summoned(){
        $childclass = get_called_class();
        return $childclass::isSingleton()
            && isset(self::$_minions[$childclass])
            ? true: false;
    }

//    public static function isSingleton(){
//        return self::$singleton;
//    }

    public static function getMinions(){
        return self::$_minions;
    }

    public static function getMinionById($minion_id){
        return isset(self::$_minions[$minion_id])
            ? self::$_minions[$minion_id]
            : false;
    }

    public function getMinionId(){
        if ($this->_minion_id == false){
            $this->_minion_id = uniqid(time());
        }
        return $this->_minion_id;
    }

    public function unsummon(){
        $childclass = get_called_class();
        if ($childclass::isSingleton()) {
            unset(self::$_minions[$childclass]);
        } else {
            unset(self::$_minions[$childclass][$this->getMinionId()]);
        }

        return $this;
    }
}