<?php
namespace Utopia\Components\Helper;

use Utopia\Components\Core\MallocworksException;
use Utopia\Components\Core\ComponentRoot;

class HelperFactory extends ComponentRoot
{
	static public function isSingleton(){
		return true;
	}

	public function initialize($mixed=false){
	}

    public function __call($name, $arguments=array()){
        if (strpos($name,'_') == false){
            throw new \Exception("invalid helper name $name");
        }
        list($class, $function) = explode('_', $name, 2);

        $classname = __NAMESPACE__.'\\'.ucfirst(strtolower($class)) ."Helper";
        if(!class_exists($classname)){
            throw new MallocworksException("invalid class name $classname");
        }

        return call_user_func_array(array($classname, $function),  $arguments);
    }

    /**
     * fetch all helper names
     *
     * @param $name
     */
    public function getHelperNames($name) {
        $rtn = array();

        $classname = __NAMESPACE__.'\\'.ucfirst(strtolower($name)) ."Helper";
        if(!class_exists($classname)){
            throw new MallocworksException("invalid class name $classname");
        }

        $r = new \ReflectionClass($classname);
        $methods = $r->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach($methods as $method) {
            $rtn[] = $method->getName();
        }

        return $rtn;
    }
}