<?php
namespace Utopia\Components\Workflow;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Core\DataObject;
use Utopia\Components\Logger\Logger;

class WorkflowKeeper extends ComponentRoot{

    private $_data;         //DataObject

    static function isSingleton(){
        return true;
    }

    public function __construct() {
        $this->_data = new DataObject();
    }

    public function initialize($mixed=false){
    }

    public function addWorkflow(BaseWorkflow $workflow) {
        $key = $this->getWorkflowKey($workflow);
        $this->_data->{$key} = $workflow;
        return $this;
    }

    public function getWorkflow($key){
        return $this->_data->get($key, false);
    }

    public function getWorkflowNames(){
        return $this->_data->keys('');
    }

    public function getWorkflowKey(BaseWorkflow $workflow) {
        $controller = $workflow->getController();
        $url    = $controller->getUrl();
        $params = $controller->getRawInputs();
        $method = $controller->getMethod();

        $key = $this->_generateWorkflowKey($url, $method, $params);
        return $key;
    }

    public function getWorkflowKeyFromConfig($url, $method, $params){
        $key = $this->_generateWorkflowKey($url, $method, $params);
        return $$key;
    }

    public function checkWorkflowExists($url, $method, $params){
        $key = $this->_generateWorkflowKey($url, $method, $params);
        return $this->_data->exists($key);
    }

    private function _generateWorkflowKey($url, $method, $params) {
        ksort($params);
        return "{$method}_"
            .urlencode($url)
            ."_"
            .md5(serialize($params));
    }
}