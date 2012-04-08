<?php
namespace Utopia\Components\DataParser;

use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Core\MallocworksException;
use Utopia\Components\Core\DataObject;
use Utopia\Components\Core\ComponentRoot;

class DataParser extends ComponentRoot{

    //inheret from ComponentRoot class
    static protected $singleton = true;
    //own variables
    private $_conf=null;             //config from
    private $_engines=null;          //actual classes
    private $_engineClasses=null;    //classnames

    /*
     * classes implementing ComponentRoot's implementation class
     */
    public function initialize($mixed=false){
        $engines = ConfigurationBundle::summon()
            ->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF)
            ->get_value('dataparser.engines');
        $this->registerEngines($engines);
    }

    public static function isSingleton(){
        return true;
    }

    /*
     * main classes
     */

    public function __construct() {
        $this->_engines = array();
    }

    /**
     * handles all asXXX() function calls
     * @param $name
     * @param $arguments[0], input to change, can be any value
     * @param $arguments[1], type of $arguments[0], optional
     */
    public function __call($name, $arguments) {
        if (strcmp(mb_substr($name,0,2),'as')!=0) {
            throw new MallocworksException("function $name is not defined");
        }

        $target = mb_strtolower(mb_substr($name,2));
        switch($target) {
            case 'array':
                $array = is_array($arguments[0])?
                    $arguments[0]:
                    call_user_func_array(array($this,'parse'),$arguments);
                return is_array($array)? $array: array();
                break;
//            case 'dataobj':
//            case 'dataobject':
//                $array = is_array($arguments[0])?
//                    $arguments[0]:
//                    call_user_func_array(array($this,'parse'),$arguments);
//                return is_array($array)
//                    ? new DataObject($array)
//                    : new DataObject(array());
//                break;
            default:
                if (is_array($arguments[0])) {
                    $array = $arguments[0];
                } else {
                    if (!in_array($target, array_keys($this->_engineClasses))) {
                        throw new MallocworksException("cannot find $target parser");
                    }
                    $array = call_user_func_array(array($this,'parse'),$arguments);
                }
                return $this->pack($array,$target);
        }
    }

    /**
     * registering engines available and their classnames
     * @param array $engines
     */
    public function registerEngines($engines) {
        foreach($engines as $name => $className) {
            $this->_engineClasses[mb_strtolower($name)] = $className;
        }
    }

    /**
     * entry point for packing data
     * output type is for sure a must to include
     * @param array  $input
     * @param string $type
     */
    public function pack($input, $type) {
        $type = mb_strtolower($type);
        if (!isset($this->_engineClasses[$type])) {
            throw new MallocworksException("parser type $type is not supported");
        }
        $className = $this->_engineClasses[$type];

        if (!isset($this->_engines[$className])){
            $this->_engines[$className] = new $className();
        }
        $engine = $this->_engines[$className];
        if ($engine->acceptPack($input)) {
            try {
                return $engine->pack($input);
            } catch(MallocworksException $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * entry point for parsing data
     * @param mixed  $input
     * @param string $type
     */
    public function parse($input, $type='noidea', $args=array()) {
        $type = mb_strtolower($type);

        if ($type == 'noidea') {
            return $this->_parseInput($input, $args);
        } else {
            return $this->_parseSpecific($input, $type, $args);
        }
    }

    /**
     * parse based on one type, would be faster than guessing
     * @param mixed  $input
     * @param string $type
     */
    private function _parseSpecific($input, $type, $args=array()) {
        if (!isset($this->_engineClasses[$type])) {
            throw new MallocworksException("parser type $type is not supported");
        }
        $className = $this->_engineClasses[$type];

        if (!isset($this->_engines[$className])){
            $this->_engines[$className] = new $className();
        }
        $engine = $this->_engines[$className];

        if ($engine->acceptExtract($input)) {
            return $engine->extract($input, $args);
        } else {
            return false;
        }
    }

    /**
     * try all parsers for output
     * @param $input
     */
    private function _parseInput($input, $args=array()){
        foreach ($this->_engineClasses as $type => $className) {
            try {
                $rtn = $this->_parseSpecific($input, $type, $args);
                if ($rtn !== false) {
                    return $rtn;
                }
            } catch(\Exception $e) {
                //we are trying every parse blindly here, errors are natural
            }
        }

        // nothing works, throw Exception
        throw new MallocworksException("cannot find an appropriate parser to extract input");
    }
}