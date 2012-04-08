<?php
namespace Utopia\Components\Validator;

use Utopia\Components\Core\DataObject;
use Utopia\Components\Logger\Logger;
use Utopia\Components\Core\ComponentRoot;

class Validator extends ComponentRoot{

    protected $_raw = null;
    protected $_filtered = null;
    protected $_validation = null;

    static public function isSingleton(){
        return false;
    }

    public function initialize($mixed=false){}

    public function __construct(){}

    public function validate(ValidationObject $validation, array $inputs){
        $this->_raw = new DataObject($inputs);
        $this->_filtered = new DataObject();
        $this->_validation = $validation;
        return $this->_validate();
    }

    public function getFiltered($type='array'){
        return 0 == strcasecmp('dataobject', $type)
            ? $this->_filtered
            : $this->_filtered->get('');
    }

    public function getFilteredValue($path) {
        return $this->_filtered->get($path);
    }

    public function _validate($basepath=''){
        $logger = Logger::summon();

        //default value
        $allOkay = ComponentRoot::OKAY;

        //run through all elements
        $args = empty($basepath)
            ? $this->_validation->getArgs()
            : $this->_validation->getChildren($basepath);
        foreach ($args as $arg){
            $argpath = !empty($basepath)
                ? $basepath.'>'.$arg
                : $arg;

            //add default if applicable
            $val_to_be_filtered = $this->_raw->exists($argpath)
                ? $this->_raw->{$argpath}
                : $this->_validation->getDef($argpath);

            //check req
            if ($this->_validation->getReq($argpath)
                && is_null($val_to_be_filtered)
            ){
                //error, required field missing
                $logger->log("required field '$argpath' missing", ComponentRoot::LEVEL_WARNING);
                $allOkay = ComponentRoot::ERROR;
            }

            //next if there is no value and it is not an error
            if (is_null($val_to_be_filtered)) {
                continue;
            }

            //check generic types
            if (!is_null($val_to_be_filtered)
                && ComponentRoot::OKAY !== $this->_checkInputType($val_to_be_filtered, $this->_validation->getType($argpath))
            ) {
                //error, field is not of required type
                $logger->log("field '$argpath' is not of type '".$this->_validation->getType($argpath)."'", ComponentRoot::LEVEL_WARNING);
                $allOkay = ComponentRoot::ERROR;
            }

            //apply filters
            if ($this->_validation->getFilter($argpath)) {
                $rtncode = $this->_performFilter($val_to_be_filtered, $this->_validation->getFilter($argpath));
                if (ComponentRoot::OKAY !== $rtncode) {
                    //error, filter error
                    $logger->log("filter error for field '$argpath'", ComponentRoot::LEVEL_WARNING);
                    $allOkay = ComponentRoot::ERROR;
                }
            }

            //add value to filtered DataObject
            $this->_filtered->{$argpath} = $val_to_be_filtered;

            //check children if exist
            if (array() != $this->_validation->getChildren($argpath)) {
                $rtncode = $this->_validate($argpath);
                if (ComponentRoot::OKAY !== $rtncode) {
                    //error, children error
                    $logger->log("children error for field '$argpath'", ComponentRoot::LEVEL_WARNING);
                    $allOkay = ComponentRoot::ERROR;
                }
            }
        }

        return $allOkay;
    }

    protected function _checkInputType(&$val, $type) {
        //special treatment if type is an array, meaning the value must be one of the items in the array
        if (is_array($type)) {
            return in_array($val, $type)
                ?  ComponentRoot::OKAY
                :  ComponentRoot::ERROR;
        }


        //remove (xx) from types like 'integer(1)'
        $mod_type = false === strpos($type, '(')
            ? strtolower($type)
            : strstr(strtolower($type),'(',true);
        switch($mod_type) {
            case 'string':
            case 'text':
                if (!is_string($val)) {
                   return ComponentRoot::ERROR;
                }
                break;
            case 'int':
            case 'integer':
                $rtn = function_exists('filter_var')
                	? filter_var($val, FILTER_VALIDATE_INT)
                	: (is_int($val)? $val: false);
                if (false === $rtn) {
                    return ComponentRoot::ERROR;
                } else {
                    $val = $rtn;
                }
                break;
            case 'float':
            case 'double':
                $rtn = function_exists('filter_var')
                	? filter_var($val, FILTER_VALIDATE_FLOAT)
                	: (is_float($val)? $val: false);
                if (false === $rtn) {
                    return ComponentRoot::ERROR;
                } else {
                    $val = $rtn;
                }
                break;
            case 'bool':
            case 'boolean':
                $rtn = function_exists('filter_var')
                	? filter_var($val, FILTER_VALIDATE_BOOLEAN)
                	: (is_bool($val)? $val: false);
                if (false === $rtn) {
                    return ComponentRoot::ERROR;
                } else {
                    $val = $rtn;
                }
                break;
            case 'datetime':
                if (is_string($val) || is_int($val)) {
                    $rtn = strtotime($val);
                    if (false === $rtn) {
                        return ComponentRoot::ERROR;
                    } else {
                        $val = date('Y-m-d H:i:s', $rtn);
                    }
                } else {
                    return ComponentRoot::ERROR;
                }
                break;
            case 'array':
                if (!is_array($val)) {
                   return ComponentRoot::ERROR;
                }
                break;
            default:
                //check if the type is a regex
                if (0 == preg_match($type, $val)) {
                    Logger::summon()->log("type '$type' is not supported and not a regex", ComponentRoot::LEVEL_ERROR);
                    return ComponentRoot::ERROR;
                }
        }

        return ComponentRoot::OKAY;
    }

    protected function _performFilter(&$val, $filter) {
        //TODO: implement later
        return ComponentRoot::OKAY;
    }
}