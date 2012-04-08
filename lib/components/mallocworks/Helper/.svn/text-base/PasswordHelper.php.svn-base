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

class PasswordHelper
{
	private static $salt = '146a1fe64bc5d8e25c6c6fa810accfbc45703d2d6856b52a720f75b7f3dfe27df7acfb7323cda6a2d305fdf3980c32a2';

	/**
     * generate crumb
     *
     * @return string
     */
	public static function gencrumb()
	{
		$browser = isset($_SERVER['HTTP_USER_AGENT'])? md5($_SERVER['HTTP_USER_AGENT']): '';
		$time = isset($_SERVER['REQUEST_TIME'])?$_SERVER['REQUEST_TIME']: time();
		$ip = isset($_SERVER['REMOTE_ADDR'])?md5($_SERVER['REMOTE_ADDR']): md5('0.0.0.0');
		return self::easyencrypt("$browser::$time::$ip");
	}

    /**
     * check crumb
     *
     * @param  string $crumb input
     *
     * @return boolean
     */
    public static function checkcrumb($crumb)
    {
    	$expire_time = 1800;

        $array = explode('::',self::easydecrypt($crumb));
        //decrypt error
        if (count($array)!=3){
        	return false;
        }
        list($broswer_crumb, $time_crumb, $ip_crumb) = $array;

        $browser = isset($_SERVER['HTTP_USER_AGENT'])? md5($_SERVER['HTTP_USER_AGENT']): '';
        $time = isset($_SERVER['REQUEST_TIME'])?$_SERVER['REQUEST_TIME']: time();
        $ip = isset($_SERVER['REMOTE_ADDR'])?md5($_SERVER['REMOTE_ADDR']): md5('0.0.0.0');

        return ( strcmp($browser,$broswer_crumb) != 0
            || $ip != $ip_crumb
            || $time >= $time_crumb + $expire_time
        )? false: true;
    }

	/**
     * not too secure decryption
     *
     * @param  string $string to decrypt
     * @return string
     */
	public static function easyencrypt($string)
	{
        $hash = md5(self::$salt);
        $obscured =  self::__scramble($hash,$string, '');
        return $obscured.substr(self::$salt, 23, 10).strlen($string);
	}

	/**
	 * not too secure decryption
	 *
	 * @param  string $string to decrypt
	 * @return string
	 */
	public static function easydecrypt($string)
	{
        $pair = explode(substr(self::$salt, 23, 10), $string);
        if (!is_numeric($pair[1])){
        	return false;
        }
        //return true;
        return self::__harvest($pair[0], $pair[1], '');
	}

    /**
     *
     * @param string to hash
     *
     * @return string
     */
    public static function hashgen($string)
    {
    	return self::__hash($string);
    }


    /**
     *
     * @param hash to check
     * @param string to check against
     *
     * @return bool
     */
    public static function hashcheck($hash, $check)
    {
    	return self::__hash($check, $hash) == false? false: true;
    }


    /**
     *
     * @param string to generate password
     *
     * @return string
     */
    public static function genpassword($string)
    {
    	return md5($string);
    }


    /**
     *
     * @param string $encrypt to check against
     * @param string $raw     current input
     *
     * @return string
     */
    public static function checkpassword($encrypt, $raw)
    {
    	return strcmp(md5($raw),$encrypt)==0? true: false;
    }


    /**
     * from http://php.net/manual/en/function.sha1.php
     *
     * @param $hash
     * @param $salt
     * @param $password
     *
     * @return unknown_type
     */
	private static function __scramble($hash, $salt, $password)
	{
	  $k = strlen($password); $j = $k = $k > 0 ? $k : 1; $p = 0; $index = array(); $out = ""; $m = 0;
	  for ($i = 0; $i < strlen($salt); $i++)
	  {
	    $c = substr($password, $p, 1);
	    $j = pow($j + ($c !== false ? ord($c) : 0), 2) % (strlen($hash) + strlen($salt));
	    while (array_key_exists($j, $index))
	      $j = ++$j % (strlen($hash) + strlen($salt));
	    $index[$j] = $i;
	    $p = ++$p % $k;
	  }
	  for ($i = 0; $i < strlen($hash) + strlen($salt); $i++)
	    $out .= array_key_exists($i, $index) ? $salt[$index[$i]] : $hash[$m++];
	  return $out;
	}


	/**
	 * from http://php.net/manual/en/function.sha1.php
	 *
	 * @param $obscured
	 * @param $slen
	 * @param $password
	 *
	 * @return unknown_type
	 */
	private static function __harvest($obscured, $slen, $password)
	{
	  $k = strlen($password); $j = $k = $k > 0 ? $k : 1; $p = 0; $index = array(); $out = "";
	  for ($i = 0; $i < $slen; $i++)
	  {
	    $c = substr($password, $p, 1);
	    $j = pow($j + ($c !== false ? ord($c) : 0), 2) % strlen($obscured);
	    while (in_array($j, $index))
          $j = ++$j % strlen($obscured);
	    $index[$i] = $j;
	    $p = ++$p % $k;
	  }
	  for ($i = 0; $i < $slen; $i++)
	    $out .= $obscured[$index[$i]];
	  return $out;
	}


	/**
	 * from http://php.net/manual/en/function.sha1.php
	 *
	 * @param $password
	 * @param $obscured
	 * @param $algorithm
	 *
	 * @return unknown_type
	 */
	private static function __hash($password, $obscured = NULL, $algorithm = "sha1")
	{
	  // whether to use user specified algorithm
	  //$mode = in_array($algorithm, hash_algos());
	  $mode = false;
	  // generate random salt
	  $salt = uniqid(mt_rand(), true);
	  // hash it
	  $salt = $mode ? hash($algorithm, $salt) : sha1($salt);
	  // get the length
	  $slen = strlen($salt);
	  // compute the actual length of salt we will use
	  // 1/8 to 1/4 of the hash, with shorter passwords producing longer salts
	  $slen = max($slen >> 3, ($slen >> 2) - strlen($password));
	  // if we are checking password against a hash, harvest the actual salt from it, otherwise just cut the salt we already have to the proper size
	  $salt = $obscured ? self::__harvest($obscured, $slen, $password) : substr($salt, 0, $slen);
	  // hash the password - this is maybe unnecessary
	  $hash = $mode ? hash($algorithm, $password) : sha1($password);
	  // place the salt in it
	  $hash = self::__scramble($hash, $salt, $password);
	  // and hash it again
	  $hash = $mode ? hash($algorithm, $hash) : sha1($hash);
	  // cut the result so we can add salt and maintain the same length
	  $hash = substr($hash, $slen);
	  // ... do that
	  $hash = self::__scramble($hash, $salt, $password);
	  // and return the result
	  return $obscured && $obscured !== $hash ? false : $hash;
	}
}
?>