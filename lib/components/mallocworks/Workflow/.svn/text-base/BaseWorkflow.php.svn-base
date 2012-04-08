<?php
namespace Utopia\Components\Workflow;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Core\DataObject;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\Logger\Logger;

abstract class BaseWorkflow extends ComponentRoot implements IWorkflow{

    private $_data;         //DataObject

    static function isSingleton(){
        return false;
    }

    public function __construct() {
        $this->_data = new DataObject();
    }

    public function initialize($mixed=false){
        ConfigurationBundle::summon()->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF);
        $this->setController($mixed);
        return $this;
    }

    public function setController($controller){
        if (is_a($controller, 'BaseController')) {
            Logger::summon()->log('controller inputted in setController() function is not a controller object', ComponentRoot::LEVEL_WARNING);
            return $this;
        }
        $this->_data->controller = $controller;
        return $this;
    }

    public function getController(){
        return $this->_data->get("controller", null);
    }

    public function render($overrides=array()) {
        $controller = $this->getController();

        //insert and filter inputs
        switch($controller->getMethod()){
            case 'POST':
                $inputs = isset($overrides['POST'])? $overrides['POST']: $_POST;
                break;
            case 'PUT':
                $inputs = isset($overrides['PUT'])? $overrides['PUT']: $_POST;
                break;
            case 'DELETE':
                $inputs = isset($overrides['DELETE'])? $overrides['DELETE']: $_POST;
                break;
            case 'GET':
            default:
                $inputs = isset($overrides['GET'])? $overrides['GET']: $_GET;
                break;
        }
        $controller->setParams($inputs);
        return $this;
    }
}