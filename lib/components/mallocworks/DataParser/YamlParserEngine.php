<?php
namespace Utopia\Components\DataParser;

use Utopia\Components\DataParser\IParserEngine;
use \Symfony\Components\Yaml;

class YamlParserEngine implements IParserEngine {
    public function __construct() {

    }

    public function getType() {
        return Parser::TYPE_YAML;
    }

    public function acceptExtract($input) {
        if (200>mb_strlen($input)
            && file_exists($input)
            && !is_dir($input)
        ){
            $ext = trim(mb_strrchr($input, '.'),'.');
            if (in_array(mb_strtolower($ext), array('yml', 'yaml'))) {
                return true;
            } else {
                return false;
            }
        } elseif (preg_match('/[\w]+:\/\//', $input)) {
            //assume we can open all urls
            return true;
        } else {
            try {
                $rtn = Yaml\Yaml::load($input);
                //$rtn = syck_load($input);
                return true;
            } catch(Exception $e) {
                return false;
            }
        }
    }

    public function acceptPack($data) {
        if (is_array($data)) {
            return true;
        } else {
            return false;
        }
    }

    public function extract($input, $args=array()) {
        try {
            if (preg_match('/[\w]+:\/\//', $input)) {
                //get data from uri/stream source
                $ch = curl_init();
                curl_setopt ($ch, CURLOPT_URL, $input);
                curl_setopt ($ch, CURLOPT_HEADER, 0);
                ob_start();
                curl_exec ($ch);
                curl_close ($ch);
                $data = ob_get_clean();
            } else {
                //yaml can handle the rest
                $data = $input;
            }
            $rtn = Yaml\Yaml::load($data);
            //$rtn = syck_load($data);
            return $rtn;
        } catch(Exception $e) {
            return false;
        }
    }

    public function pack($data) {
        try {
            $rtn = Yaml\Yaml::dump($data, 6);
            //$rtn = syck_dump($data);
            return $rtn;
        } catch (Exception $e) {
            return false;
        }
    }
}
