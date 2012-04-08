<?php
/**
 * Text helper functions
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
 * Text helper functions
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

class TextHelper
{
    /**
     * fetch web page content
     *
     * @param string $str    string to be cut
     *
     * @return string
     */
	public static function getPureWords($str)
	{
	    $str = strip_tags($str); //remove html tags
	    $str = preg_replace("/[^a-zA-Z0-9\s]/", "", $str); //remove all except alphabets and numbers
	    $str = preg_replace("/\s+/", " ", $str);   //remove more than one space
	    $str = str_replace(array("\n", "\t"), "", $str); //remove line breaks
	    return $str;
	}

    /**
     * fetch web page content
     *
     * @param string $str    string to be cut
     *
     * @return string
     */
	public static function tokenize($str)
	{
	    $str = self::getPureWords($str);
	    $str = strtolower(trim($str));
        $strip = self::getStrip();
	    $str = str_replace($strip, ' ', $str); //remove stop words

	    $words = explode(" ", $str);
	    array_walk($words, 'trim');
	    return $words;
	}

    /**
     * fetch web page content
     *
     * @return string
     */
	public static function getStrip()
	{
	    $filename = Autoload::getFilePath('search_strip', array(), 'txt');
	    if ($filename === false) {
	        echo "cannot read search_strip.txt, assume nothing to strip...\n";
	        return array();
	    } else {
	        $strip = file($filename);
	        foreach($strip as &$s){
	            $s = " ". trim($s). " ";
	        }
	        return $strip;
	    }
	}

    /**
     * remove spaces, tabs and line breaks on html
     *
     * @param string $str    string to be cut
     *
     * @return string
     */
    public static function stripHTML($str='')
    {
    	return preg_replace('/>[ \n\r\t]*</', '><', $str);
    }

}
?>