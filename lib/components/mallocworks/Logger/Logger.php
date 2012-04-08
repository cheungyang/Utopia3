<?php
namespace Utopia\Components\Logger;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Core\DataObject;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

class Logger extends ComponentRoot{

    private $_data;         //DataObject

    static function isSingleton(){
        return true;
    }

    public function initialize($mixed=false){
        $c = ConfigurationBundle::summon()
            ->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF);

        $fileConfs = $c->get_value('logger.files', array());
        foreach($fileConfs as $conf){
            $this->registerFileStream($conf['filename'], $conf['level']);
        }

        $responseConfs = $c->get_value('logger.responses', array());
        foreach($responseConfs as $conf){
            $this->registerResponse(
                $conf['level'],
                isset($conf['classes'])? $conf['classes']: array()
            );
        }

        $printConfs = $c->get_value('logger.prints', array());
        foreach($printConfs as $conf){
            $this->registerPrint(
                $conf['level'],
                isset($conf['color'])? $conf['color']: "off",
                isset($conf['classes'])? $conf['classes']: array()
            );
        }

        $errorlogConfs = $c->get_value('logger.errorlog', array());
        foreach($errorlogConfs as $conf){
            $this->registerErrorlog(
                $conf['level'],
                isset($conf['color'])? $conf['color']: "off",
                isset($conf['classes'])? $conf['classes']: array()
            );
        }

        date_default_timezone_set($c->{'logger>region'});
    }

    public function __construct() {
        $this->_data = new DataObject();
    }

    public function __destruct() {
        //remove file handlers
        $this->_data->setPointer('filehandlers');
        foreach($this->_data as $fh) {
            fclose($fh);
        }
    }

    public function registerFileStream($filename, $level=999) {
        if (empty($filename)){
            return $this;
        }

        $this->_data->push("file>{$level}", $filename);
        if (!isset($this->_data->{"filehandlers>$filename"})) {
            $dir = dirname($filename);
            if (!file_exists($dir)){
                mkdir($dir, 0755, true);
            }
            $handle = fopen($filename, "a");
            if ($handle !== false) {
                $this->_data->{"filehandlers>$filename"} = $handle;
            } else {
                throw new MallocworksException("file $filename cannot be opened", LEVEL_ERROR);
            }
        }
        return $this;
    }

    public function registerResponse($level=999, $classes=array()) {
        $this->_data->push("response>{$level}", array(
            'classes'=>$classes
        ));
        return $this;
    }

    public function registerPrint($level=999, $color="off", $classes=array()) {
        $this->_data->push("print>{$level}", array(
        	'color'=> 'off'===$color? false: true,
            'classes'=>$classes
        ));
        return $this;
    }

    public function registerErrorlog($level=999, $color="off", $classes=array()) {
        $this->_data->push("errorlog>{$level}", array(
        	'color'=> 'off'===$color? false: true,
            'classes'=>$classes
        ));
        return $this;
    }

    public function log($string, $level=ComponentRoot::LEVEL_ERROR) {
        $pdate = date('[Y-m-d H:i:s]');
        $pdate_colored = $this->_getColorString($pdate,
            ConfigurationBundle::summon()->{"logger>timestamp_foreground"},
            ConfigurationBundle::summon()->{"logger>timestamp_background"}
        );
        $pconf = ConfigurationBundle::summon()->{"logger.log.config>".$level};
        $pclass= $this->_getCalledClass();

        //print to log file
        $this->_data->setPointer('file');
        foreach($this->_data as $reg_level => $files) {
            if ($level < $reg_level) {
                continue;
            }
            foreach($files as $filename) {
                $handle = $this->_data->{"filehandlers>$filename"};
                if (is_resource($handle)) {
                    fwrite($handle, "$pdate{$pconf['text']}[$pclass]$string\n");
                }
            }
        }

        //print to screen
        $this->_data->setPointer('print');
        foreach($this->_data as $reg_level => $confs) {
            foreach($confs as $conf) {
                //check if the level is high enough
                if ($level < $reg_level) {
                    continue;
                }
                //check if the class should be recorded
                if (!empty($conf['classes']) && !in_array(strstr($pclass, "::", true), $conf['classes'])){
                    continue;
                }
                //print colored string or not
                if ($conf['color']) {
                    echo $pdate_colored
                        .$this->_getColorString("{$pconf['text']}[$pclass]", $pconf['foreground'], $pconf['background'])
                        ."$string\n";
                } else {
                    echo "$pdate{$pconf['text']}[$pclass]$string\n";
                }
            }
        }

        //save to response
        $this->_data->setPointer('response');
        foreach($this->_data as $reg_level => $confs) {
            foreach($confs as $conf) {
                //check if the level is high enough
                if ($level < $reg_level) {
                    continue;
                }
                //check if the class should be recorded
                if (!empty($conf['classes']) && !in_array(strstr($pclass, "::", true), $conf['classes'])){
                    continue;
                }
                $this->_data->push('response_data', htmlentities("$pdate{$pconf['text']}[$pclass]$string"));
            }
        }

        //print to error_log
        $this->_data->setPointer('errorlog');
        foreach($this->_data as $reg_level => $confs) {
            foreach($confs as $conf) {
                //check if the level is high enough
                if ($level < $reg_level) {
                    continue;
                }
                //check if the class should be recorded
                if (!empty($conf['classes']) && !in_array(strstr($pclass, "::", true), $conf['classes'])){
                    continue;
                }
                //print colored string or not
                if ($conf['color']) {
                    error_log("[log]".
                        $pdate_colored
                        .$this->_getColorString("{$pconf['text']}[$pclass]", $pconf['foreground'], $pconf['background'])
                        .$string
                    );
                } else {
                    error_log("[log]$pdate{$pconf['text']}[$pclass]$string");
                }
            }
        }
    }

    public function backtrace(){
        $bt = debug_backtrace();
        $i = 1;
        while($i<10 && isset($bt[$i])){
            $o = $bt[$i];
            $this->log("[$i]{$o['class']}::{$o['function']}({$o['line']})", ComponentRoot::LEVEL_DEBUG);
            $i++;
        }
    }

    public function getResponses($format="") {
        $responses = $this->_data->get('response_data', array());
        switch($format){
            case 'html':
                $config = ConfigurationBundle::summon()->get_value("logger.log.config");
                foreach($responses as &$r){
                    //22 is the length of the timestamp
                    $type = substr($r, 21, strpos($r,']',22)-20);
                    $levels = array_keys($config);
                    $i = 0;
                    while(isset($config[$levels[$i]]["text"]) && $type != $config[$levels[$i]]["text"]){ $i++;}
                    if (isset($config[$levels[$i]]['foreground'])){
                        $foreground = $config[$levels[$i]]['foreground'];
                        if (!empty($foreground)){
                            $r = str_replace($type, '<font color="'.$foreground.'">'.$type.'</font>', $r);
                        }
                    }
                    $r = '<font color="green">'.substr($r, 0, 21).'</font>'.substr($r, 21);
                }
                break;
            default:
        }
        //should not delete as it will be share in many layers
        //$this->_data->del('response_data'.SEP.$identifier);
        return $responses;
    }

    private function _getColorString($string, $fg="", $bg="") {
        $colored_string = '';

        $fg = ConfigurationBundle::summon()->get_value("logger.color.foreground>$fg", "");
        if (!empty($fg)) {
            $colored_string .= "\033[" . $fg . "m";
        }

        $bg = ConfigurationBundle::summon()->get_value("logger.color.foreground>$bg", "");
        if (!empty($bg)) {
            $colored_string .= "\033[" . $bg . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";
        return $colored_string;
    }

    private function _getCalledClass(){
        $arr = debug_backtrace(false);
        foreach($arr as $i){
            if ("Utopia\Components\Logger\Logger"!=$i['class']) {
                $pos = strrpos($i['class'], "\\");
                return 0<$pos
                    ? substr($i['class'], $pos+1).'::'.$i['function']
                    : $i['class'].'::'.$i['function'];
            }
        }
        return "";
    }
}