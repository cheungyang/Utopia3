<?php
/**
 * @version CVS: $Id: VersionCvsIdTagSniff.php,v 1.2 2008/12/17 15:33:56 ycheung Exp $
 *
 */

namespace Utopia\Components\Loader;

require "lib/components/mallocworks/Core/MallocworksException.php";
require "lib/components/mallocworks/Core/IComponentRoot.php";
require "lib/components/mallocworks/Core/ComponentRoot.php";
require "lib/components/mallocworks/Loader/Loader.php";

use Utopia\Components\Core\MallocworksException;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Loader\Loader;

class Autoload extends ComponentRoot{

    //other class data
    private $_data = array();    //array

    /**
     * inherent from ComponentRoot class
     *
     * @return this
     */
    public static function isSingleton(){
        return true;
    }

    /**
     * inherent from ComponentRoot class
     *
     * @return bool
     */
    public function initialize($mixed=false){
        $conf = parse_ini_file(dirname(__FILE__).'/master.ini', true);
        foreach($conf['autoload.namespaces'] as $ns => $path){
            $this->includeNamespace($ns, $path);
        }
        return $this;
    }

    /**
     * constructor
     *
     */
    public function __construct() {
        if (!defined('DS'))  { define('DS', '/'); };

        spl_autoload_register(array($this, 'autoLoader'));
        //print_r(spl_autoload_functions());
    }

    /**
     * including namespaces into autoload scope
     *
     * @param string $namespace
     * @param string $directory
     * @param int    $depth     how deep the autoloader look into
     *
     * @throws MallocworksException
     *
     * @return this
     */
    public function includeNamespace($namespace, $directory, $depth=99){
        if (($path = realpath($directory)) !== false) {
            $this->_data[$path] = array(
                'path' => $path,
                'namespace' => $namespace,
                'depth' => $depth
            );
        } else {
            throw new MallocworksException("'$directory' is not a valid path");
        }
        return $this;
    }

    /**
     * handing an array of inclusion
     *
     * @param array $items array of namespaces:directories
     */
    public function includeNamespaces(array $items){
        foreach($items as $item){
            call_user_func_array(array($this, 'includeNamespace'), $item);
        }
        return $this;
    }


    /**
     * get includd namespaces
     *
     * @return array
     */
    public function getIncludedNamespaces() {
        $namespaces = array();
        foreach($this->_data as $searchdir){
            if (isset($searchdir['namespace'])){
                $namespaces[] = $searchdir['namespace'];
            }
        }
        return $namespaces;
    }

    /**
     * get all included information, mainly for Loader class
     *
     * @return array
     */
    public function getIncludedInfo() {
        return $this->_data;
    }

    /**
     * autoload classes from classname
     *
     * @param string $className class name to be loaded
     *
     * @return this
     */
    public function autoLoader($className)
    {
        //1. check if class already included
        if (class_exists($className, false) || interface_exists($className, false)) {
            return false;
        }

        //2. check if it is cached

        //3. check if we can load if with php5.3 folder arrangement
        $fullpath = Loader::summon()->getFilePathByClass($className);
        if ($fullpath !== false){
            include $fullpath;
            return true;
        }

        //4. sorry not found
        return false;
    }
}
