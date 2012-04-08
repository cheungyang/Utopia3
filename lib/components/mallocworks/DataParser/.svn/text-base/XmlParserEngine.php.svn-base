<?php
namespace Utopia\Components\DataParser;

use Utopia\Components\DataParser\IParserEngine;

class XmlParserEngine implements IParserEngine {

    public function __construct() {

    }

    public function getType() {
        return Parser::TYPE_XML;
    }

    public function acceptExtract($input) {
        if (200>mb_strlen($input)
            && file_exists($input)
            && !is_dir($input)
        ){
            //extension check
            $ext = trim(mb_strrchr($input, '.'),'.');
            if (mb_strtolower($ext) == 'xml') {
                return true;
            } else {
                return false;
            }
        } elseif (preg_match('/[\w]+:\/\//', $input)) {
            //assume we can open all urls
            return true;
        } else {
            try {
                $rtn = new \SimpleXMLElement($input);
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

    /**
     * (non-PHPdoc)
     * @param mixed $input
     * @param array $args   array('flatten'=>bool)
     *
     * @see Components/mallocworks/DataParser/Utopia\Components\DataParser.IParserEngine::extract()
     */
    public function extract($input, $args=array()) {
        try {
            if (file_exists($input) && !is_dir($input)){
                //get data from file
                $data = file_get_contents($input);
            } elseif (preg_match('/[\w]+:\/\//', $input)) {
                //get data from uri/stream source
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

            $flatten = isset($args['flatten'])? $args['flatten']: true;
            $targetchild = isset($args['targetchild'])? $args['targetchild']: '';
            $offset = isset($args['offset'])? $args['offset']: 0;
            $limit = isset($args['limit'])? $args['limit']: 100;

            $xmlobj = new \SimpleXMLElement($data);
            return array($xmlobj->getName()=>$this->_simpleXMLToArray($xmlobj, $flatten, $targetchild, $offset, $limit));
        } catch(Exception $e) {
            return false;
        }
    }

    public function pack($data) {
        return $this->_prettyXML(
            '<?xml version="1.0" encoding="UTF-8"?>'.$this->_manualAtoX($data)
        );
/*
        try {
            $xmlobj = $this->_arrayToSimpleXmlElement($data);

            if (is_null($xmlobj)){
                return '<?xml version="1.0" encoding="UTF-8"?>';
            } else {
                $str = html_entity_decode($xmlobj->asXML());
                //$str = html_entity_decode($this->_prettyXml($xmlobj->asXML()));
                return $str;
            }
        } catch (Exception $e) {
            return false;
        }*/
    }


    private function _manualAtoX($data, $parentkey=''){
        $str = '';
        if (is_array($data) && !empty($data)){
            foreach($data as $key => $val) {
                if (!empty($parentkey)) {
                    $key = $parentkey;
                }

                if ($val === '') {
                    $str .= "<$key/>";
                } elseif (is_array($val)) {
                    //check if value keys are numbers
                    reset($val);
                    if (current($val) == false) {
                        $is_seq = false;
                    } else {
                        while(current($val)!=false && is_int(key($val))){
                            next($val);
                        }
                        $is_seq = current($val) == false? true: false;
                    }

                    if ($is_seq) {
                        $str .= $this->_manualAtoX($val, $key);
                    } else {
                        $str .= sprintf("<%s>%s</%s>", $key, $this->_manualAtoX($val), $key);
                    }
                } else {
                    $str .= sprintf("<%s>%s</%s>", $key, $val, $key);
                }
            }
        }
        return $str;
    }


    /**
     * convert array to simpleXmlElement, then to XML string
     *
     * @param string $key of the root array
     * @param array $data
     *
     * @return string
     */
    private function _arrayToSimpleXmlElement($data, $xml=null) {
        $valueKey='@value';
        $attributesKey='@attributes';
        $childrenKey='@children';

        foreach($data as $key => $val) {
            if ($key == $attributesKey) {
                //not support for now
            } elseif ($key == $childrenKey) {
                //pass to next level
                $xml = $this->_arrayToSimpleXmlElement($val, $xml);
            } elseif (!is_array($val)) {
                //bottom reached
                //$safe_value = "<![CDATA[".preg_replace('/&(?!\w+;)/', '&amp;', $val)."]]>";
                $safe_value = preg_replace('/&(?!\w+;)/', '&amp;', htmlentities($val));
                //echo ">>1: $safe_value\n";

                if (is_null($xml)){
                    $xml = new \SimpleXMLElement("<$key>{$safe_value}</$key>");
                } else {
                    $xml->addChild($key, $safe_value);
                }
            } elseif (isset($val[$valueKey])) {
                //bottom reached, non flatten mode
                //$safe_value = "<![CDATA[".preg_replace('/&(?!\w+;)/', '&amp;', $val)."]]>";
                $safe_value = preg_replace('/&(?!\w+;)/', '&amp;', htmlentities($val));
                //echo ">>2: $safe_value\n";

                if (is_null($xml)){
                    $xml = new \SimpleXMLElement("<$key>{$safe_value}</$key>");
                } else {
                    $xml->addChild($key, $safe_value);
                }
            } else {
                //go deeper
                if (is_null($xml)){
                    $xml = new \SimpleXMLElement("<$key/>");
                    $this->_arrayToSimpleXmlElement($val, $xml, $key);
                } else {
                    //check if value keys are numbers
                    reset($val);
                    if (current($val) == false) {
                        $is_seq = false;
                    } else {
                        while(current($val)!=false && is_int(key($val))){
                            next($val);
                        }
                        $is_seq = current($val) == false? true: false;
                    }

                    //handling for each case (is_seq)
                    if ($is_seq) {
                        //probably an array sequence
                        foreach($val as $subkey => $subarray) {
                            if (is_array($subarray)) {
                                $subxml = $xml->addChild($key);
                                $this->_arrayToSimpleXmlElement($subarray, $subxml);
                            } else {
                                $safe_value = preg_replace('/&(?!\w+;)/', '&amp;', htmlentities($subarray));
                                //echo ">>3: $safe_value\n";

                                $xml->addChild($key, $safe_value);
                            }
                        }
                    } else {
                        $subxml = $xml->addChild($key);
                        $this->_arrayToSimpleXmlElement($val, $subxml);
                    }
                }
            }
        }
        return $xml;
    }


    /**
     * Converts a simpleXML element into an array. Preserves attributes and everything.
     * You can choose to get your elements either flattened, or stored in a custom index that
     * you define.
     * For example, for a given element
     * <field name="someName" type="someType"/>
     * if you choose to flatten attributes, you would get:
     * $array['field']['name'] = 'someName';
     * $array['field']['type'] = 'someType';
     * If you choose not to flatten, you get:
     * $array['field']['@attributes']['name'] = 'someName';
     * _____________________________________
     * Repeating fields are stored in indexed arrays. so for a markup such as:
     * <parent>
     * <child>a</child>
     * <child>b</child>
     * <child>c</child>
     * </parent>
     * you array would be:
     * $array['parent']['child'][0] = 'a';
     * $array['parent']['child'][1] = 'b';
     * ...And so on.
     * _____________________________________
     * @param simpleXMLElement $xml         the XML to convert
     * @param boolean          $flatten     Choose wether to flatten values
     *                                      or to set them under a particular index.
     * @param string           $targetchild child name for the target
     * @param int              $offset      start offset
     * @param int              $limit       how many children returned
     * @return array the resulting array.
     */
    private function _simpleXMLToArray($xml, $flatten, $targetchild, $offset, $limit){
        $valueKey='@value';
        $attributesKey='@attributes';
        $childrenKey='@children';

        $return = array();
        if(!($xml instanceof \SimpleXMLElement)){return $return;}
        $name = $xml->getName();

        $_value = trim((string)$xml);
        if(mb_strlen($_value)==0){$_value = null;};

        if($_value!==null){
            if(!$flatten){$return[$valueKey] = $_value;}
            else{$return = $_value;}
        }

        //for targetchild offset counting
        $target_offset = 0;
        $target_count = 0;

        $children = array();
        $first = true;
        foreach($xml->children() as $elementName => $child){
            //check if we need to extract just a limited subset of this child
            if (!empty($targetchild) && $elementName == $targetchild){
                if ($target_offset++ < $offset){
                    //skip until offset reached
                    continue;
                } elseif($target_count++ >= $limit) {
                    //stop when count reached
                    break;
                }
            }

            //value fetching
            $value = $this->_simpleXMLToArray($child, $flatten, $targetchild, $offset, $limit);
            if(isset($children[$elementName])){
                if($first){
                    $temp = $children[$elementName];
                    unset($children[$elementName]);
                    $children[$elementName][] = $temp;
                    $first=false;
                }
                $children[$elementName][] = empty($value)? '':$value;
            }
            else{
                $children[$elementName] = empty($value)? '':$value;
            }
        }
        if(count($children)>0){
            if(!$flatten){$return[$childrenKey] = $children;}
            else{$return = array_merge($return,$children);}
        }

        if (!$flatten) {
            $attributes = array();
            foreach($xml->attributes() as $name=>$value){
                $attributes[$name] = trim($value);
            }
            if(count($attributes)>=0){
                if(!$flatten){$return[$attributesKey] = $attributes;}
                else{$return = array_merge($return, $attributes);}
            }
        }

        return $return;
    }

    private function _prettyXML($string) {
        //ob_end_clean();print_r($string); die();

        //add opening tag
        $string = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8"?>', $string);

        /**
         * put each element on it's own line
         */
        $string =preg_replace("/>\s*</",">\n<",$string);

        /**
         * each element to own array
         */
        $xmlArray = explode("\n",$string);

        /**
         * holds indentation
         */
        $currIndent = 0;

        /**
         * set xml element first by shifting of initial element
         */
        $string = array_shift($xmlArray) . "\n";

        foreach($xmlArray as $element) {
            /** find open only tags... add name to stack, and print to string
             * increment currIndent
             */

            if (preg_match('/^<([\w])+[^>\/]*>$/U',$element)) {
                $string .=  str_repeat(' ', $currIndent) . $element . "\n";
                $currIndent += 4;
            }

            /**
             * find standalone closures, decrement currindent, print to string
             */
            elseif ( preg_match('/^<\/.+>$/',$element)) {
                $currIndent -= 4;
                $string .=  $currIndent>0?
                    str_repeat(' ', $currIndent) . $element . "\n":
                    $element . "\n";
            }
            /**
             * find open/closed tags on the same line print to string
             */
            else {
                $string .=  $currIndent>0?
                    str_repeat(' ', $currIndent) . $element . "\n":
                    $element . "\n";
            }
        }

        return $string;
    }
}
