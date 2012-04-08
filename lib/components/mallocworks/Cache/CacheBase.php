<?php
namespace Utopia\Components\Cache;

use Utopia\Components\Cache\ICacheable;
use Utopia\Components\Logger\Logger;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

abstract class CacheBase extends ComponentRoot implements ICacheable{

    private $_enable=false;
    private $_ttl=0;

    static public function isSingleton(){
        return true;
    }

    public function initialize($mixed=false) {
        if (isset($mixed['cache.generic.enable'])) {
            $this->setEnable($mixed['cache.generic.enable']);
        } else {
            $cb = ConfigurationBundle::summon();
            $cb->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF);
            $this->setEnable($cb->get_value('cache.generic.enable'));
        }
    }

    public function setEnable($bool) {
        $this->_enable = $bool;
    }

    public function isEnabled() {
        return $this->_enable;
    }
}