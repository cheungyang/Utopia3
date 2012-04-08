<?php
namespace Utopia\Components\Cache;

use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

use Utopia\Components\Core\DataObject;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Logger\Logger;
use Utopia\Components\Cache\ICache;
use Utopia\Components\Cache\CacheBase;

class FileCache extends CacheBase
{
    private $_data = null;
    private $_cache_file = false;

    public function initialize($mixed=false){
        parent::initialize($mixed);

        $this->_cache_file = isset($mixed['cache.file.filename'])
            ? $mixed['cache.file.filename']
            : ConfigurationBundle::summon()->{'cache.file.filename'};
        if (file_exists($this->_cache_file)){
            $content = file_get_contents($this->_cache_file);
            $content_array = unserialize($content);
            $this->_data = new DataObject($content_array);
        } else {
            $this->_data = new DataObject();
        }
    }

    public function __destruct(){
        if ($this->isEnabled()) {
            $content_string = serialize($this->_data->get(''));
            $fp = @fopen($this->_cache_file, 'w');
            if ($fp) {
                fwrite($fp, $content_string);
                fclose($fp);
            } else {
                if (Logger::is_summoned()) {
                    Logger::summon()->log("cannot open file '{$this->_cache_file}' for writing", ComponentRoot::LEVEL_WARNING);
                }
            }
        }
    }

    public function add($key, $value, $ttl=0){
        if ($this->isEnabled()){
            $this->_data->set($key, array(
                '_data'=>$value,
                '_ttl' =>$ttl,
                '_time'=>time()
            ));
        }
        return $this;
    }

    public function get($key){
        if ($this->isEnabled()){
            //TTL constraint
            if (true == $this->_isExpired($key)){
                if (Logger::is_summoned()) {
                    Logger::summon()->log("key '$key' expired", ComponentRoot::LEVEL_DEBUG);
                }
                return ComponentRoot::ERROR;
            }
            //return real data
            return $this->_data->{"$key>_data"};
        } else {
            return ComponentRoot::ERROR;
        }
    }

    public function delete($key){
        if ($this->isEnabled()){
            $this->_data->del($key);
        }
        return $this;
    }

    public function exists($key){
        if ($this->isEnabled()){
            return $this->_data->exists($key)? true: false;
        }
        return ComponentRoot::ERROR;
    }

    private function _isExpired($key){
        if ($this->_data->exists($key)){
            return 0<$this->_data->get("$key>_ttl", 0)
                && $this->_data->get("$key>_time",0)+$this->_data->get("$key>_ttl",0) < time()
                ? true: false;
        } else {
            if (Logger::is_summoned()) {
                Logger::summon()->log("key '$key' not exist for expiration test", ComponentRoot::LEVEL_WARNING);
            }
            return ComponentRoot::ERROR;
        }
    }
}