<?php
namespace Utopia\Components\ClassDispatcher;

use Utopia\Components\Core\DataObject;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\DataParser\DataParser;
use Utopia\Components\Logger\Logger;

class ClassDispatcher extends ComponentRoot
{
    protected $_data;         //DataObject of actual routes
    protected $_route_files;  //array of route files

    static public function isSingleton(){
        return true;
    }

    public function initialize($mixed=false){
        $this->_data = new DataObject();

        ConfigurationBundle::summon()
        ->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF);

        $this->_loadRoutes();
    }

    public function __construct(){}

    public function getRouteNames(){
        return $this->_data->keys('routes', array());
    }

    public function getRoute($route, $path=''){
        return empty($path)
        ? $this->_data->{"routes>$route"}
        : $this->_data->get("routes>$route>$path", false);
    }

    public function getRouteByController($controller_name, $path=''){
        $routes = $this->getRouteNames();
        foreach($routes as $route) {
            if ($controller_name == $this->_data->get("routes>$route>controller",false)){
                return $this->getRoute($route, $path);
            }
        }
        return false;
    }

    /**
     * get regex for acceptUrl by settings
     * base: eg "/entity/load"
     * args: eg array(
     *      'key1' => '\w'      //accepts all letter/alphabets
     *      'key2' => '0-9A-Z'  //you know this...
     * );
     *
     * @return string
     */
    public function getUrlRegex($route){
        $route = $this->getRoute($route);

        //base
        $pattern = "/^";
        $pattern .= str_replace('/', '\/', $route['base_uri']);

        //params
        if (!empty($route['required']) && is_array($route['required'])) {
            foreach($route['required'] as $key => $expopts) {
                $exp = $expopts['type'];
                $pattern .= strpos($exp, "\/")
                ? "\/(?P<$key>[$exp]+)"        //include "/" and get as much as possible
                : "\/(?P<$key>[$exp^\/]+?)";   //exclude "/" and get lazy
            }
        }

        //other excessive params and extension
        $pattern .= "(\/(?P<extra>.*))*?(.(?P<ext>(htm|html|php|json|css|js)))?$/";
        return $pattern;
    }

    public function acceptUrl($route, $url, $method){
        $urlopts = parse_url($url);

        //check url pattern first
        $pattern = $this->getUrlRegex($route);
        if (0 == preg_match($pattern, $urlopts['path'], $matches)) {
            Logger::summon()->log("{$urlopts['path']} not match regex for $route", ComponentRoot::LEVEL_TRIVAL);
            return false;
        }

        //check if there is method restriction
        $methods = $this->getRoute($route, 'methods');
        if (is_array($method) && !in_array(mb_strtoupper($method, $methods))) {
            Logger::summon()->log("{$urlopts['path']} matched $route, but not supporting method '$method'", ComponentRoot::LEVEL_DEBUG);
            return false;
        }

        //TODO: check authenication

        //filter and save information
        $data = array();
        if (isset($matches['ext'])){
            $data['_ext'] = $matches['ext'];
        }
        if (isset($matches['extra'])){
            $data['_extra'] = $matches['extra'];
        }

        $required = $this->getRoute($route, 'required');
        if (is_array($required)){
            foreach(array_keys($required) as $r) {
                $data[$r] = $matches[$r];
            }
        }

        $this->_data->{"accepts>".urlencode($url)} = array(
            'route' => $route,
            'data'  => $data
        );

        Logger::summon()->log("{$urlopts['path']} accepted by route $route", ComponentRoot::LEVEL_DEBUG);
        return true;
    }

    public function dispatch($url, $method){
        $route_names = $this->getRouteNames();
        foreach($route_names as $r) {
            if ($this->acceptUrl($r, $url, $method)) {
                //check workflow class
                $workflow = $this->getWorkflowClass($url);
                if (!class_exists($workflow)){
                    Logger::summon()->log("workflow class for url '$url' cannot be found", ComponentRoot::LEVEL_ERROR);
                    return ComponentRoot::ERROR;
                }

                //check controller class
                $controller = $this->getControllerClass($url);
                if (!class_exists($controller)){
                    Logger::summon()->log("controller class for url '$url' cannot be found", ComponentRoot::LEVEL_ERROR);
                    return ComponentRoot::ERROR;
                }

                //create a new object
                $ctrclass = new $controller($url, $method);
                $wfclass = call_user_func(array($workflow, 'summon'));
                $wfclass->initialize($ctrclass);
                return $wfclass;
            }
        }
        Logger::summon()->log("url '$url' cannot find a route to match url", ComponentRoot::LEVEL_WARNING);
        return ComponentRoot::ERROR;
    }

    public function getControllerClass($url){
        $route = $this->_data->{"accepts>".urlencode($url).">route"};
        $class = $this->_data->{"routes>$route>controller"};
        return $class;
    }

    public function getWorkflowClass($url){
        $route = $this->_data->{"accepts>".urlencode($url).">route"};
        $class = $this->_data->get(
        	"routes>$route>workflow",
            ConfigurationBundle::summon()->{'classdispatcher.workflows.error'}
        );
        return $class;
    }

    /**
     * A backdoor method for phpunits only
     * @param string $url
     * @param array $name-$val pair
     */
    public function setArguments($url, $data){
        $this->_data->set("accepts>".urlencode($url).">data", $data);
        return $this;
    }    
    
    public function getArguments($url, $path=''){
        return empty($path)
        ? $this->_data->get("accepts>".urlencode($url).">data", array())
        : $this->_data->get("accepts>".urlencode($url).">data>$path", false);
    }

    protected function _loadRoutes(){
        $files = ConfigurationBundle::summon()->{"classdispatcher.routes"};

        foreach($files as $file) {
            if (is_array($this->_route_files) && in_array($file, $this->_route_files)) {
                continue;
            }

            //load files
            $route_info = DataParser::summon()->asArray($file);
            if (empty($route_info)){
                Logger::summon()->log("route info empty for '$file', wrong filename?", ComponentRoot::LEVEL_WARNING);
            }
            if (is_array($route_info)) {
                $this->_data->merge('routes', $route_info);
                $this->_route_files[] = $file;
            } else {
                Logger::summon()->log("failed loading route file '$file'", ComponentRoot::LEVEL_ERROR);
            }
        }
    }
}
