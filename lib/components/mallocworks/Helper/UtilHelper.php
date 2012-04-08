<?php
/**
 * Misc helper functions
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
 * Misc helper functions
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

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Logger\Logger;

class UtilHelper
{
    /**
     * generate rand id
     *
     * @param $seed seed to generate id
     *
     * @return string
     */
    public static function generateId($seed='')
    {
    	return empty($seed)?
    	   uniqid(rand()):
    	   uniqid($seed);
    }

    /**
     * generate one-to-one hashkey
     *
     * @param $seed seed to generate hash
     *
     * @return string
     */
    public static function generateHash($seed)
    {
        return md5(serialize($seed));
    }

    /**
     * camelize strings
     *
     * @param string $id string to H::obj()->camelize
     *
     * @return string
     */
    public static function camelize($id)
    {
        $id = strtolower($id);
        return preg_replace(array('/(^|_|-)+(.)/e', '/\.(.)/e'), array("strtoupper('\\2')", "'_'.strtoupper('\\1')"), $id);
    }

    /**
     * trun camelized string to underscored string
     *
     * @param string $id H::obj()->camelized string
     *
     * @return string
     */
    public static function underscore($id)
    {
        return strtolower(preg_replace(array('/_/', '/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'), array('.', '\\1_\\2', '\\1_\\2'), $id));
    }

    /**
     * fetch web page content
     *
     * @param string $url 			url
     * @param mixed  $queryparams   query string or array
     * @param string $method 		request method
     * @param array|string $headers http headers
     *
     * @return string
     */
    public static function curl($url, $queryparams='', $method="GET", $headers=array())
    {
        if (is_array($queryparams)) {
            $queryparams = http_build_query($queryparams);
        }

        switch (strtoupper($method)) {
            case 'POST':
                $options = array(
                    CURLOPT_HEADER         => 0,
                    CURLOPT_HTTPHEADER     => is_array($headers)? array_values($headers): array($headers),
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_POST           => 1,
                    CURLOPT_URL            => $url,
                    CURLOPT_POSTFIELDS     => $querystr
                );
                break;
            case 'PUT':
                if (!file_exists($querystr)){
                    throw new Exception("path $querystr is not a valid filename");
                }
                try{
                    $fh = fopen($querystr, 'r');
                } catch(Exception $e){
                    throw new Exception("error opening file $querystr, ", $e->getMessage());
                }
                $options = array(
                    CURLOPT_HEADER         => 0,
                    CURLOPT_HTTPHEADER     => is_array($headers)? array_values($headers): array($headers),
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_PUT            => 1,
                    CURLOPT_URL            => $url,
                    CURLOPT_INFILE         => $fh,
                    CURLOPT_INFILESIZE     => filesize($querystr)
                );
                break;
            case 'DELETE':
                break;
            case 'GET':
            default:
                $options = array(
                    CURLOPT_HEADER         => 0,
                    CURLOPT_HTTPHEADER     => is_array($headers)? array_values($headers): array($headers),
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_CUSTOMREQUEST  => $method,
                    CURLOPT_TIMEOUT        => 5,
                    CURLOPT_URL            => empty($querystr)? $url: "$url?$querystr"
                );
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
		if ($result = curl_exec($ch)){
		    $info = curl_getinfo($ch);
		    Logger::summon()->log("time:{$info['total_time']}, size:{$info['size_download']} -> '$url'", ComponentRoot::LEVEL_DEBUG);
		} else {
		    Logger::summon()->log("curl to url '$url' failed", ComponentRoot::LEVEL_WARNING);
		}
		curl_close ($ch);
		return $result;
    }

    /**
     * serialize array using json_encode as it is faster
     *
     * @param array $array array to serialize
     *
     * @return void
     */
    public static function serialize($array)
    {
        //return json_encode($array);
        return serialize($array);
    }

    /**
     * serialize array using json_encode as it is faster
     *
     * @param array $array array to unserialize
     *
     * @return void
     */
    public static function unserialize($str)
    {
        //return json_decode($str);
        return unserialize($str);
    }

    /**
     * generate a random uuid by time
     *
     * @return string
     */
    public static function generateUuid($engine="random")
    {
        if ($engine=="time") {
            $chars = md5(time());
        } else {
            $chars = md5(uniqid(mt_rand(), true));
        }
        $uuid  = substr($chars,0,8) . '-';
        $uuid .= substr($chars,8,4) . '-';
        $uuid .= substr($chars,12,4) . '-';
        $uuid .= substr($chars,16,4) . '-';
        $uuid .= substr($chars,20,12);
        return $prefix . $uuid;
    }
}
?>