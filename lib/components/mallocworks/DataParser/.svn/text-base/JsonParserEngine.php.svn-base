<?php
namespace Utopia\Components\DataParser;

use Utopia\Components\DataParser\IParserEngine;

class JsonParserEngine implements IParserEngine {
    public function __construct() {

    }

    public function getType() {
        return Parser::TYPE_JSON;
    }

    public function acceptExtract($input) {
        if (200>mb_strlen($input)
            && file_exists($input)
            && !is_dir($input)
        ){
            $ext = trim(mb_strrchr($input, '.'),'.');
            if (in_array(mb_strtolower($ext), array('json', 'js'))) {
                return true;
            } else {
                return false;
            }
        } else {
            return null == json_decode($input)? false: true;
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
        if (file_exists($input) && !is_dir($input)){
            //get data from file
            $data = file_get_contents($input);
        } elseif (filter_var($input, FILTER_VALIDATE_URL) != false) {
            //get data from curl
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $input);
            curl_setopt ($ch, CURLOPT_HEADER, 0);
            ob_start();
	    	curl_exec ($ch);
		    curl_close ($ch);
		    $data = ob_get_clean();
        } else {
            //input is ready the data
            $data = $input;
        }

        $rtn = (array)json_decode($data);
        if ($rtn == false || is_null($rtn)) {
            return false;
        }
        return $rtn;
    }

    public function pack($data) {
        $rtn = json_encode($data);
        if ($rtn == false || is_null($rtn)) {
            return false;
        }
        return $this->_prettyJson($rtn);
    }

    // Pretty print some JSON
    private function _prettyJson($json) {
        $tab = "    ";
        $new_json = "";
        $indent_level = 0;
        $in_string = false;

        $json_obj = json_decode($json);

        if($json_obj === false)
            return false;

        $json = json_encode($json_obj);
        $len = mb_strlen($json);

        for($c = 0; $c < $len; $c++){
            $char = $json[$c];
            switch($char){
                case '{':
                case '[':
                    if(!$in_string){
                        $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                        $indent_level++;
                    }else{
                        $new_json .= $char;
                    }
                    break;
                case '}':
                case ']':
                    if(!$in_string){
                        $indent_level--;
                        $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                    }else{
                        $new_json .= $char;
                    }
                    break;
                case ',':
                    if(!$in_string){
                        $new_json .= ",\n" . str_repeat($tab, $indent_level);
                    }else{
                        $new_json .= $char;
                    }
                    break;
                case ':':
                    if(!$in_string){
                        $new_json .= ": ";
                    }else{
                        $new_json .= $char;
                    }
                    break;
                case '"':
                    if($c > 0 && $json[$c-1] != '\\'){
                        $in_string = !$in_string;
                    }
                default:
                    $new_json .= $char;
                    break;
            }
        }
        return $new_json;
    }
}