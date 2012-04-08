<?php
namespace Utopia\Components\Cache;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Logger\Logger;
use Utopia\Components\Cache\ICache;
use Utopia\Components\Cache\CacheBase;

class ApcCache extends CacheBase
{
    public function initialize($mixed=false){
        parent::initialize($mixed);
        if (!$this->isEnabled() && function_exists('apc_clear_cache')){
            apc_clear_cache();
        }
    }

    public function add($key, $value, $ttl=0){
        if ($this->isEnabled()){
            if (!function_exists('apc_store')){
                Logger::summon()->log("function 'apc_store' does not exist", ComponentRoot::LEVEL_WARNING);
            } elseif (false === apc_store($key, $value, $ttl)) {
                Logger::summon()->log("failed storing key '$key'", ComponentRoot::LEVEL_WARNING);
            }
        }
        return $this;
    }

    public function get($key){
        if ($this->isEnabled()){
            $success = false;
            if (function_exists('apc_fetch')){
                $data = apc_fetch($key, $success);
            } else {
                Logger::summon()->log("function 'apc_fetch' does not exist", ComponentRoot::LEVEL_WARNING);
            }
            if (false === $success) {
                Logger::summon()->log("failed fetching key '$key'", ComponentRoot::LEVEL_WARNING);
                return ComponentRoot::ERROR;
            } else {
                return $data;
            }
        } else {
            return ComponentRoot::ERROR;
        }
    }

    public function delete($key){
        if ($this->isEnabled()){
            if (!function_exists('apc_delete')){
                Logger::summon()->log("function 'apc_delete' does not exist", ComponentRoot::LEVEL_WARNING);
            } elseif (false === apc_delete($key)){
                Logger::summon()->log("failed delete key '$key'", ComponentRoot::LEVEL_WARNING);
            }
        }
        return $this;
    }

    public function exists($key){
        if ($this->isEnabled()
            && function_exists('apc_exists')
        ){
            return apc_exists($key);
        }
        return ComponentRoot::ERROR;
    }
}