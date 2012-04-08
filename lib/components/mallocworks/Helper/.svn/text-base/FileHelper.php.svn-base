<?php
/**
 * File operations helper functions
 *
 * PHP Version 5.2
 *
 * @category   Utopia_Core
 * @package    Utopia
 * @subpackage Helper
 * @author     Alva Cheung <mallocworks@gmail.com>
 * @license    mallocworks http://mallocworks.com/license
 * @version    SVN: <svn_id>
 * @link       http://mallocworks.com
 * @since      2010-01-04 15:00:00
 */

/**
 * File operations helper functions
 *
 * @category   Utopia_Core
 * @package    Utopia
 * @subpackage Helper
 * @author     Alva Cheung <mallocworks@gmail.com>
 * @license    mallocworks http://mallocworks.com/license
 * @version    Release: <package_version>
 * @link       http://mallocworks.com
 * @since      2010-01-04 15:00:00
 */
namespace Utopia\Components\Helper;

class FileHelper
{
    /**
     * make directories
     *
     * @param array dirs to create
     * @param bool  recursively create dir
     *
     * @return bool
     */
    public static function makeDirs($dirs, $recursive=false, $mode=0755)
    {
        if (!is_array($dirs)){
            $dirs = array($dirs);
        }

        $done = true;
        foreach($dirs as $dir){
            if (!(file_exists($dir)) && !mkdir($dir, $mode, $recursive)){
            	echo 'X';
                $done = false;
            } else {
                echo '.';
            }
        }
        return $done;
    }

    /**
     * move files
     *
     * @param array  $filepair from, to
     * @param string $postfix  postfix to search
     * @param int    $mode     file mode
     *
     * @return bool
     */
    public static function moveFiles($filepair, $postfix='', $mode='') {
        return self::copyFiles($filepair, $postfix, $mode, true);
    }

    /**
     * copy files
     *
     * @param array  $filepair from, to
     * @param string $postfix  postfix to search
     * @param int    $mode     file mode
     *
     * @return bool
     */
    public static function copyFiles($filepair, $postfix='', $mode='', $movefile=false) {
        if (empty($postfix)){
            $postfix = array();
        } elseif (!is_array($postfix)){
            $postfix = array($postfix);
        }

        $done = true;
        foreach($filepair as $from => $to){
            if (!self::__copyFile(array($from, $to), $postfix, $mode, $movefile)){
                $done = false;
            }
        }
        return $done;
    }

    private static function __copyFile($filepair, $postfix, $mode=0, $movefile=false) {

        if(is_file($filepair[0])){
            //copy/movefiles
            if ($movefile?
                rename($filepair[0], $filepair[1]):
                copy($filepair[0], $filepair[1])
            ) {
                echo '.';
                //change mode
                if($mode!=0) {
                    chmod($filepair[1], $mode);
                }
                return true;
            } else {
                echo 'X';
                return false;
            }
        } elseif(is_dir($filepair[0])){
            //mkdir dest dir
            @mkdir($filepair[1], 0755, true);
            //find files/dirs in the dir
            $srcs = glob(rtrim($filepair[0],'/').'/*');

            //form related dest
            $paths = array();
            if (!empty($srcs)) {
                foreach($srcs as $src){
                    //skip cases
                    if (!is_dir($src) && !empty($postfix) && !in_array(substr(strrchr($src, '.'), 1 ), $postfix)) {
                        continue;
                    }
                    $paths[] = array(
                        $src,
                        str_replace($filepair[0],$filepair[1],$src)
                    );
                }
            }

            $done = true;
            foreach($paths as $path){
                if (!self::__copyFile($path, $postfix, $mode, $movefile)){
                    $done = false;
                }
            }
        } else {
            echo "{$filepair[0]} not a file or directory";
            return false;
        }
        return $done;
    }

    /**
     * replace content in files
     *
     * @param array files
     * @param array from text, to text
     *
     * @return bool
     */
    public static function filterContents($files, $filters)
    {
        if (!is_array($files)){
            $files = array($files);
        }

        $done = true;
        foreach($files as $file){
            if (!self::__filterContent($file, $filters)){
                $done = false;
            }
        }
        return $done;
    }

    private static function __filterContent($file, $filters)
    {
        $done = true;
        if(is_file($file)){
            $inputs = array();
            $str = file_get_contents($file);
            foreach($filters as $pattern => $replace) {
                $str = preg_replace($pattern, $replace, $str);
            }
            if (!file_put_contents($file, $str)){
                $done = false;
                echo 'X';
            } else {
                echo '.';
            }
        } elseif(is_dir($file)){
            $scan = glob(rtrim($file,'/').'/*');
            $done = true;
            if (!$scan) {
                $scan = array();
            }
            foreach($scan as $path){
                if (!self::__filterContent($path, $filters)){
                    $done = false;
                }
            }
        } else {
            echo "{$filepair[0]} not a file or directory";
            return false;
        }

        return $done;
    }

    /**
     * delete directories
     *
     * @param array dirs to create
     *
     * @return bool
     */
    public static function deleteDirs($dirs)
    {
        if (!is_array($dirs)){
            $dirs = array($dirs);
        }

        $done = true;
        foreach($dirs as $dir){
            if (!self::__deleteDir($dir)){
                $done = false;
            }
        }
        return $done;
    }

    /**
     * delete a directory in a recursive
     *
     * @param array dirs to create
     *
     * @return bool
     */
    private static function __deleteDir($dirname)
    {
        if(is_file($dirname)){
            if (unlink($dirname)) {
                echo '.';
                return true;
            } else {
                echo 'X';
                return false;
            }
        }
        elseif(is_dir($dirname)){
            $scan = glob(rtrim($dirname,'/').'/*');
            $done = true;
            if (!$scan) {
                $scan = array();
            }
            foreach($scan as $path){
                if (!self::__deleteDir($path)){
                    $done = false;
                }
            }
            //attempt to remove dir
            if (!@rmdir($dirname)){
                $done = false;
                echo 'X';
            } else {
            	echo '.';
            }
            return $done;
        } else {
            //file not exist, return true
            return true;
        }
    }

    /**
     * list all files in a directory
     *
     * @param array|string dirnames
     * @param string       extension whitelist of what to show
     *
     * @return array
     */
    public static function listFiles($dirnames, $postfix='') {
        if (!is_array($dirnames)) {
            $dirnames = array($dirnames);
        }

        $files = array();
        foreach($dirnames as $dirname){
            $files = array_merge($files, self::__listFile($dirname, $postfix));
        }
        sort($files);
        return $files;
    }

    private static function __listFile($dirname, $postfix) {
        if(is_file($dirname)){
            return array($dirname);
        } elseif(is_dir($dirname)){
            //find files/dirs in the dir
            $srcs = glob(rtrim($dirname,'/').'/*');
            $files = array();
            //get deeper level files
            if (!empty($srcs)) {
                foreach($srcs as $src) {
                    if (is_dir($src)){
                        $files = array_merge($files, self::__listFile($src, $postfix));
                    } elseif (empty($postfix) || substr(strrchr($src, '.'), 1 ) == $postfix)  {
                        $files[] = $src;
                    }
                }
            }
            return $files;
        } else {
            echo "$dirname not a file or directory";
            return array();
        }
    }
}
?>