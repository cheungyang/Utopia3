<?php
/**
 * @version CVS: $Id: VersionCvsIdTagSniff.php,v 1.2 2008/12/17 15:33:56 ycheung Exp $
 *
 */

namespace Utopia\Components\Loader;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Core\MallocworksException;

class Loader extends ComponentRoot {

    //other data
    private static $_fileext = array(
    	'php'    => array('.php'),
        'conf'   => array('.yml', 'xml', 'ini'),
        'smarty' => array('.tpl'),
    	'template' => array('.tpl', '.php', '.html'),
        'css'    => array('.css'),
        'js'     => array('.js'),
    );
    private static $_blacklist = array(
    	'.', '..', '.svn', 'tags', '.metadata'
    );

    /**
     * inherent from ComponentRoot class
     *
     * @return bool
     */
    public static function isSingleton(){
        return true;
    }

    /**
     * inherent from ComponentRoot class
     *
     * @return this
     */
    public function initialize($mixed=false){
    }

    public function __construct() {
    }

    /**
     * use registered namespaces to get included file
     *
     * @param string $className class name to include
     *
     * @return false|string
     */
    public function getFilePathByClass($className){
        $data = Autoload::summon()->getIncludedInfo();

        foreach ($data as $searchdir) {
            $namespaced = str_replace('\\', DS, str_replace($searchdir['namespace'], $searchdir['path'], $className));
            //if replacement took place, find php extension
            if ($namespaced != $className) {
                foreach(self::$_fileext['php'] as $extformat) {
                    $fullpath = $namespaced.$extformat;
                    if (file_exists($fullpath) && !is_dir($fullpath)) {
                        return $fullpath;
                    }
                }
            }
        }
        return false;
    }

	/**
     * perform filename search
     *
     * @param string $filename        string without extension
     * @param array  $fileNameFormats array("%s.php")
     * @param array  $directories     array(array('dir', 'tracelevel'))
     * @param bool   $forcesearch     force search to be done
     *
     * @return string                 absolute filename if found; false if not found
     */
    public function getFilePath($filename, $fileType='php', $directories=array(), $forcesearch=false)
    {
        $fileext = self::$_fileext;
    	$fileNameFormats = isset($fileext[$fileType])? $fileext[$fileType]: array("%s.$fileType");

        //get registered directory for searching
        if (!is_array($directories)) {
            $directories = array(array($directories, 99));
        } elseif (empty($directories)) {
            $directories = Autoload::summon()->getIncludedInfo();
        }

        while (!empty($directories)) {
            $dir = array_shift($directories);
            $path = isset($dir['path'])? $dir['path']: $dir[0];
            $depth = isset($dir['depth'])? $dir['depth']: $dir[1];

            if (file_exists($path)) {
                //find files
                foreach ($fileNameFormats as $fileNameFormat) {
                    $fullpath = $path.DS.$filename.$fileNameFormat;
                    if (file_exists($fullpath) && !is_dir($fullpath)) {
                        return $fullpath;
                    }
                }

                //if not found, find subdirectories
                if ($depth > 0) {
                    $subdir = array();
                    if ($handle = opendir($path)) {
                        while (false !== ($file = readdir($handle))) {
                            if (!in_array($file, self::$_blacklist) && is_dir($path.DS.$file)) {
                                array_unshift($directories, array($path.DS.$file, $depth-1));
                            }
                        }
                    } else {
                       //dir $path is not a directory, continue with next dir
                    }
                }
            }
        }
        return false;
    }

    /**
     * get class names of a selected namespace
     *
     * @param string $tgt_namespace namespace name
     * @param bool   $forcesearch   ignore cache or not
     *
     * @param array of class names
     */
    public function getNamespaceClasses($tgt_namespace, $forcesearch=false) {
        $data = Autoload::summon()->getIncludedInfo();

        //get filenams
        $files_info = $this->getNamespaceFiles($tgt_namespace);
        //get classes from filenames
        $return_classes = array();
        foreach ($files_info as $file_info){
            foreach($file_info['files'] as $file){
                foreach ($data as $searchdir) {
                    $classname = str_replace($searchdir['path'], $searchdir['namespace'], $file);
                    if ($classname != $file) {
                        $return_classes[] = mb_strstr(str_replace(DS, "\\", $classname), ".", true);
                        break;
                    }
                }
            }
        }
        return $return_classes;
    }

    /**
     * get the latest modified time for all classes in a namespace
     *
     * @param string $tgt_namespace namespace name
     * @param bool   $forcesearch   ignore cache
     *
     * @return int linux timestamp
     */
    public function getNamespaceModtime($tgt_namespace, $forcesearch=false) {
        //get filenames by recursive search (expensive)
        $files_info = $this->getNamespaceFiles($tgt_namespace);

        //check latest modification time
        $lastmod = 0;
        foreach($files_info as $file_info) {
            foreach($file_info['files'] as $file) {
                $modtime = filemtime($file);
                if ($modtime > $lastmod) {
                    $lastmod = $modtime;
                }
            }
        }
        return $lastmod;
    }

    /**
     * get all files information inside in a namespace for further processing
     *
     * @param string $tgt_namespace namespace name
     *
     * @return array
     */
    public function getNamespaceFiles($tgt_namespace) {
        $fileNameFormats = self::$_fileext['php'];

        //get registered directory for searching
        //check if target namespace class exists
        $directories = array();
        $data = Autoload::summon()->getIncludedInfo();
        foreach ($data as $searchdir) {
            $namespaced = str_replace('\\', DS, str_replace($searchdir['namespace'], $searchdir['path'], $tgt_namespace));
            //if replacement took place, find php extension
            if (($namespaced != $tgt_namespace) && is_dir($namespaced)) {
                $directories[] = array($namespaced, $tgt_namespace);
            }
        }

        //search classes
        $return_filenames = array();
        while (!empty($directories)) {
            list($path, $namespace) = array_shift($directories);

            if (is_dir($path)) {
                //find subdirectories
                $subdir = array();
                $files = array();
                if ($handle = opendir($path)) {
                    while (false !== ($file = readdir($handle))) {
                        if (!in_array($file, self::$_blacklist) && is_dir($path.DS.$file)) {
                            array_unshift(
                                $directories,
                                array($path.DS.$file, $namespace.'\\'.$file)
                            );
                        } elseif (!is_dir($path.DS.$file) && in_array(mb_strstr($file, '.', false), $fileNameFormats)) {
                            $files[] = $path.DS.$file;
                        }
                    }
                }
                $return_filenames[] = array('path'=>$path, 'namespace'=>$namespace, 'files'=>$files);
            }
        }
        return $return_filenames;
    }
}
