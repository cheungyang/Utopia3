<?php
namespace Utopia\Components\ConfigurationBundle;

use Utopia\Components\Core\MallocworksException;
use Utopia\Components\DataParser\DataParser;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Core\DataObject;

class ConfigurationBundle extends ComponentRoot{

    protected $data;
    private $_cache = null;

    static public function isSingleton(){
        return true;
    }

    public function initialize($mixed=false){

    }

    public function __construct() {
        $this->data = new DataObject();
        $this->data->target = "x";

        //caching in ConfigurationBundle is specially handled
        $conf = parse_ini_file(dirname(__FILE__).'/master.ini', true);
        $instance = new $conf['configurationbundle.cache']['engine_class']();
        $instance->initialize($conf['configurationbundle.cache.options']);
        $this->_cache = $instance;
    }

    public function __destruct() {
    }

    public function __get($key) {
        return $this->get_value($key);
    }

    public function setDimensions(array $dimensions) {
        $this->data->dimensions = $dimensions;
        return $this;
    }

    public function setDimensionFile($filename) {
        $dimensions = $this->_getArrayFromFile($filename);
        $this->setDimensions($dimensions);
        return $this;
    }

    public function setMasterValues(array $values) {
        //we use .ini files for master,
        //that means it could have at most two levels of array
        foreach($values as $k1 => $v1){
            $k1 = str_replace(".", ">", $k1);
            print("K1: $k1");
            if (is_array($v1)) {
                foreach($v1 as $k2 => $v2){
                    $k2 = str_replace(".", ">", $k2);
                    print("K2: $k2");
                    $this->data->{"master>$k1>$k2"} = $v2;
                }
            } else {
                $this->data->{"master>$k1"} = $v1;
            }
        }
        return $this;
    }

    public function setMasterFile($filename) {
        $values = $this->_getArrayFromFile($filename);
        if (".ini" == strstr($filename, '.')) {
            $this->_setIniValues('master', $values);
        } else {
            $this->_setArrayValues('master', $values);
        }

        return $this;
    }

    public function setDeltaFile($filename) {
        if ($this->data->isempty('dimensions')){
            throw new MallocworksException("dimension must be configurated before delta files can be set");
        }

        $values = $this->_getArrayFromFile($filename);
        foreach($values as $dstr => $val){
            $dkeyarr = explode(";",$dstr);
            $dimensions = array();
            foreach($dkeyarr as $dkeystr){
                $dkey = explode("=",$dkeystr);
                $dimensions[$dkey[0]] = $dkey[1];
            }

            //check if dimensions are of allowed values
            $current = '';
            foreach($this->data->dimensions as $kname=>$kvals){
                if (key_exists($kname, $dimensions)){
                    if (is_null($kvals)
                        || (is_array($kvals) && in_array($dimensions[$kname], $kvals))
                    ) {
                        $current .= ">{$dimensions[$kname]}";
                    } else {
                        throw new MallocworksException("dimension value '{$dimensions[$kname]}' for key '{$kname}' in not valid");
                    }
                } else {
                    $current .= '>x';
                }
            }

            //insert depnds on file type
            if (".ini" == strstr($filename, '.')) {
                $this->_setIniValues("delta".$current, $val);
            } else {
                $this->_setArrayValues("delta".$current, $val);
            }
        }
        
        //print_r($this->data->delta);

        return $this;
    }

    public function setTargetDimensions($dimensions){
        //check if target dimensions are of allowed values
        foreach($dimensions as $kname=>$kval){
            if (!(
                in_array($kname, $this->data->keys("dimensions")) //dimension exists
                && (
                    $this->data->isempty("dimensions>$kname") //no element restrictions
                    || in_array($kval, $this->data->{"dimensions>$kname"}) //correct element
                )
            )){
                throw new MallocworksException("target value {$dimensions[$kname]} for key {$kname} in not valid");
            }
        }
        $this->data->target = $dimensions;
        return $this;
    }

    public function get_env($path, $default=false) {
        return $this->getTargetDimension($path, $default);
    }

    public function getTargetDimension($path, $default=false) {
        return $this->data->get("target>$path", $default);
    }

    public function get_value($key, $default=null) {
        $key = str_replace(".", ">", $key);
        $dkeys = $this->data->keys('dimensions');
        $tgt_data = null;

        //check delta first
        $targets =  $this->data->get("target", array());
        if (!is_array($targets)){
            $targets = array();
        }
        while (!empty($targets)){
            $tgt = 'delta';
            foreach($dkeys as $k){
                $tgt .= '>';
                $tgt .= isset($targets[$k])? $targets[$k]: 'x';
            }
            $tgt .= ">$key";
            
            //echo ">>$tgt\n";
            
            $tgt_data = $this->data->get($tgt, null);
            if (is_null($tgt_data)){
                array_pop($targets);
            } else {
                break;
            }
        }

        //if delta valus is an array that can be merged
        if (is_array($tgt_data)) {
            $tgt = "master>$key";
            $master_value = $this->data->get($tgt, null);
            if (is_array($master_value)){
                $tgt_data = $tgt_data+$master_value;
            }
        }

        //check master if delta not found
        if (is_null($tgt_data)){
            $tgt = "master>$key";
            
            //echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>$tgt\n";
            
            $tgt_data = $this->data->get($tgt, null);
        }

        if (is_null($tgt_data)){
            $tgt_data = $default;
        }
        if (is_null($tgt_data)){
            throw new MallocworksException("returns null from the get('$key') function, either the config cannot be found or the value is actually null");
        }

        return $tgt_data;
    }

    private function _setArrayValues($path, array $values) {
        $this->data->merge($path, $values);
        return $this;
    }

    private function _setIniValues($path, array $values) {
        //ini files could only have at most two levels of array
        foreach($values as $k1 => $v1){
            $k1 = str_replace(".", ">", $k1);
            if (is_array($v1)) {
                foreach($v1 as $k2 => $v2){
                    $k2 = str_replace(".", ">", $k2);
                    $this->data->{"$path>$k1>$k2"} = $v2;
                }
            } else {
                $this->data->{"$path>$k1"} = $v1;
            }
        }
        return $this;
    }

    private function _getArrayFromFile($filename) {
        if (!file_exists($filename)) {
            throw new MallocworksException("file $filename does not exist");
        }

        //check if cache contain valid
        $key = md5($filename);
        $mtime = filemtime($filename);
        if (ComponentRoot::ERROR != ($data = $this->_cache->get($key))
            && isset($data['mtime'])
            && $data['mtime'] >= $mtime
        ){
            //error_log(">>> file $filename loaded by cache");
            return $data['array'];
        } else {
            //error_log("xxx file $filename loaded realtime");
            $array = mb_strtolower(mb_substr($filename, -3))=="ini"?
                parse_ini_file($filename, true):
                DataParser::summon()->asArray($filename);

            //save to cache
            $this->_cache->add($key, array(
                'mtime' => $mtime,
                'array' => $array
            ));
            return $array;
        }
    }
}