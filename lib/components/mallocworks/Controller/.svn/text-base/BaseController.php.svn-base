<?php
namespace Utopia\Components\Controller;

use Utopia\Components\Workflow\BaseWorkflow;

use Utopia\Components\Workflow\WorkflowKeeper;
use Utopia\Components\Core\MallocworksException;
use Utopia\Components\Logger\Logger;
use Utopia\Components\Controller\IController;
use Utopia\Components\ClassDispatcher\ClassDispatcher;
use Utopia\Components\Validator\ValidationObject;
use Utopia\Components\Validator\Validator;
use Utopia\Components\DataParser\DataParser;
use Utopia\Components\Core\DataObject;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Loader;

abstract class BaseController implements IController
{
    private $_config_obj=null;
    protected $data = null;
    protected $url = '';
    protected $method = '';

    /**
     * 1. config is for "params" filtering, including $_REQUEST variables only
     *    filtered params can be fetched at $this->data->inputs
     * 2. url arguments (/hello/ycheung/123) is handled at ClassDispatcher
     *    arguments can be fetched thru $this->getArguements() method
     */
    protected $_config = <<<EOF
validation:
  GET:
    param_array: { req: true, type: 'array', def: [], info: 'please override this with proper filtering'}
  POST:
    param_array: { req: true, type: 'array', def: [], info: 'please override this with proper filtering'}
  PUT:
    param_array: { req: true, type: 'array', def: [], info: 'please override this with proper filtering'}
  DELETE:
    param_array: { req: true, type: 'array', def: [], info: 'please override this with proper filtering'}
dependencies:
  GET:
    apis: ~
    modules: ~
  POST:
    apis: ~
    modules: ~
  PUT:
    apis: ~
    modules: ~
  DELETE:
    apis: ~
    modules: ~
layout:
  templates:
    GET: ~
    POST: ~
  css:
    GET: ~
    POST: ~
  js:
    GET: ~
    POST: ~
EOF;

    public function __construct($url, $method){
        $this->url = $url;
        $this->method = mb_strtoupper($method);
        $this->data = new DataObject();
        $this->data->inputs_dirty = true;
        $this->data->{"inputs>args"} = $this->getArguments();
        $this->_config_obj = DataParser::summon()->asDataObj($this->_config);
        if (!$this->_config_obj instanceof DataObject) {
            Logger::summon()->log('cannot read config object as DataObject, config file error?', ComponentRoot::LEVEL_ERROR);
            $this->_config_obj = new DataObject();
        }
    }

    public function __get($path){
        return $this->getData($path);
    }

    public function execute(){
        switch($this->method) {
            case 'GET':    return $this->GET(); break;
            case 'POST':   return $this->POST(); break;
            case 'PUT':    return $this->PUT(); break;
            case 'DELETE': return $this->DELETE(); break;
        }
        return ComponentRoot::ERROR;
    }

    public function getValidationObject() {
        return new ValidationObject($this->_config_obj->{"validation>{$this->method}"});
    }

    public function validateParams() {
        if ($this->data->get("inputs_dirty", true)){
            //perform validation
            $validation_obj = $this->getValidationObject();
            $validator = Validator::summon();

            $rtn_code = $validator->validate($validation_obj, $this->data->get("inputs_raw>params", array()));
            if (ComponentRoot::OKAY == $rtn_code) {
                $this->data->{"inputs>params"} = $validator->getFiltered();
                $this->data->inputs_dirty = false;
            } else {
                $this->data->{"inputs>params"} = false;
                Logger::summon()->log('input validation failed', ComponentRoot::LEVEL_ERROR);
            }
        } else {
            $rtn_code = ComponentRoot::OKAY;
        }

        return $rtn_code;
    }

    /**
     * setting params, ie inputs that can be filtered by $this->_config only
     *
     * @param string|array $path_or_array
     * @param mixed $value
     */
    public function setParams($path_or_array, $value='') {
        $old_params = $this->data->{"inputs_raw>params"};

        if (empty($value) && is_array($path_or_array)){
            $this->data->merge("inputs_raw>params", $path_or_array);
        } else {
            $this->data->set("inputs_raw>params>$path_or_array", $value);
        }

        //set the current input as dirty
        $new_params = $this->data->{"inputs_raw>params"};
        if (serialize($old_params) != serialize($new_params)){
            $this->data->set("inputs_dirty", true);
        }
        return $this;
    }

    /**
     * getting all inputs, including arguments(from ClassDispatcher)
     * and parameters(from $this->setParams and filtering by $this->_config)
     *
     * @param string $path
     * @return mixed
     */
    public function getInputs($path=""){
        return empty($path)
            ? $this->data->get("inputs", array())
            : $this->data->get("inputs>$path", false);
    }

    public function getData($path){
        $prefix = strstr($path, '>', true);
        switch($prefix){
            case 'apis':
                $params = explode('>', $path, 3);
                return !empty($params[1]) && !empty($params[2])
                    ? $this->getApi($params[1])->getController()->{$params[2]}
                    : '';
            case 'modules':
                $params = explode('>', $path, 3);
                return !empty($params[1]) && !empty($params[2])
                    ? $this->getModule($params[1])->getController()->{$params[2]}
                    : '';
            case 'outputs':
                return $this->data->{$path};
            case 'params':
            case 'args':
                return $this->getInputs($path);
        }
    }

    /**
     * Do not use this for data processing!
     * @param string $path
     */
    public function getRawInputs($path=""){
        return empty($path)
            ? $this->data->get("inputs_raw", array())
            : $this->data->get("inputs_raw>$path", false);
    }

    public function getArguments($path=""){
        return ClassDispatcher::summon()->getArguments($this->url, $path);
    }

    public function getResultSet($path=""){
        return empty($path)
            ? $this->data->get('outputs', array())
            : $this->data->get("outputs>$path", false);
    }

    public function getErrors(){
        return Logger::summon()->getResponses();
    }

    public function getUrl(){
        return $this->url;
    }

    public function getMethod(){
        return $this->method;
    }

    public function getTemplate($fullpath=true){
        $template_filename = $this->_config_obj->get("layout>templates>{$this->method}", false);

        if (false!==$template_filename && true==$fullpath){
            return Loader::summon()->getFilePath($template_filename, 'template');
        } else {
            return $template_filename;
        }
    }

    public function getCss(){
        $css = $this->_config_obj->get("layout>css>{$this->method}", array());
        return is_array($css)? $css: array($css);
    }

    public function getJs(){
        $js = $this->_config_obj->get("layout>js>{$this->method}", array());
        return is_array($js)? $js: array($js);
    }

    public function getModuleNames(){
        return $this->_config_obj->keys("dependencies>{$this->method}>modules");
    }

    public function getModule($name){
        if (!$this->data->exists("dependencies>modules")){
            $this->loadDependencies();
        }
        $key = $this->data->get("dependencies>modules>$name", false);
        if (false===$key){
            return ComponentRoot::ERROR;
        } else {
            return WorkflowKeeper::summon()->getWorkflow($key);
        }
    }

    public function getApiNames(){
        return $this->_config_obj->keys("dependencies>{$this->method}>apis");
    }

    public function getApi($name){
        if (!$this->data->exists("dependencies>apis")){
            $this->loadDependencies();
        }
        $key = $this->data->get("dependencies>apis>$name", false);
        if (false===$key){
            return ComponentRoot::ERROR;
        } else {
            return WorkflowKeeper::summon()->getWorkflow($key);
        }
    }

    protected function getApiConfigs(){
        return $this->_config_obj->get("dependencies>{$this->method}>apis", array());
    }

    protected function getModuleConfigs(){
        return $this->_config_obj->get("dependencies>{$this->method}>modules", array());
    }

    protected function loadDependencies(){
        $this->_loadWorkflows("apis")
             ->_loadWorkflows("modules");
        return $this;
    }

    private function _loadWorkflows($type){
        if ("apis"!=$type && "modules"!=$type){
            Logger::summon()->log("wrong type specified, getting '$type'", ComponentRoot::LEVEL_ERROR);
            return $this;
        }

        if (!$this->data->exists("dependencies>$type")
            && ComponentRoot::OKAY === $this->validateParams()
        ){
            //get urls needs in this controller
            switch($type){
                case 'apis':    $configs = $this->getApiConfigs(); break;
                case 'modules': $configs = $this->getMOduleConfigs(); break;
            }
            $wfkeeper = WorkflowKeeper::summon();
            $this->data->{"dependencies>$type"} = array();

            //replace variables in url to values in $this->data, and fetch modules from url
            foreach($configs as $config_name => $config){
                $check = '/\{\s*([^\}^\n]*)\s*\}/';
                preg_match_all($check, $config['url'], $matches);
                foreach($matches[1] as $i => $value){
                    $config['url'] = str_replace($matches[0][$i], $this->data->{"inputs>$value"}, $config['url']);
                }
                $config['params'] = DataParser::summon()->asArray($config['params']);

                if (!$wfkeeper->checkWorkflowExists($config['url'], $config['method'], $config['params'])){
                    //new workflow
                    $workflow = ClassDispatcher::summon()->dispatch($config['url'], $config['method']);
                    if ($workflow instanceof BaseWorkflow) {
                        if ($workflow->getController()->getUrl() == $this->getUrl()){
                            Logger::summon()->log("a possible self-looping happens for url'{$config['url']}', dependency loading terminated", ComponentRoot::LEVEL_ERROR);
                        } else {
                            $workflow->getController()->setParams($config['params']);
                            //add this workflow to workflowkeeper when params are set
                            $wfkeeper->addWorkflow($workflow);
                            $key = $wfkeeper->getWorkflowKey($workflow);
                            $this->data->{"dependencies>$type>$config_name"} = $key;
                            //call loadDependencies() method for the new workflow's controller
                            $workflow->getController()->loadDependencies();
                        }
                    } else {
                        Logger::summon()->log("failed to dispatch url '{$config['url']}' for method '{$config['method']}'", ComponentRoot::LEVEL_ERROR);
                    }
                } else {
                    //workflow being created, skip creating process
                    $key = $wfkeeper->getWorkflowKeyFromConfig($config['url'], $config['method'], $config['params']);
                    $this->data->{"dependencies>$type>$config_name"} = $key;
                }
            }
        }
        return $this;
    }
}
