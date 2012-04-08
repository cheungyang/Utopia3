<?php
namespace Utopia\Components\Validator;

use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\DataParser\DataParser;
use Utopia\Components\DataModel\DataSchema;
use Utopia\Components\Core\MallocworksException;
use Utopia\Components\Logger\Logger;
use Utopia\Components\Core\DataObject;

class ValidationObject{

    protected $data = null;

    public function __construct($mixed=null){
        if (!is_null($mixed)) {
            if (is_object($mixed)) {
                //see how we can handle that
                switch (get_class($mixed)) {
                    default:
                        throw new MallocworksException("do not know how to handle input class ".get_class($mixed));
                }
            } else {
                //hopefully can be a valid DataObject
                $args = DataParser::summon()->asArray($mixed);
                $this->data = new DataObject();
                $this->data->args = $args;
            }
        } else {
            $this->data = new DataObject();
        }
    }

    public function fromSchema(DataSchema $schema, $entity) {
        $fields = $schema->getSchema($entity);
        $this->data->merge('args', $fields['fields']);
        return $this;
    }

    public function addArg($name, $type="string", $req=false, $def=null, $filter=false) {
        if ($this->data->exists("args>$name")){
            throw new MallocworksException("cannot redeclare argument $name");
        }
        $this->data->{"args>$name"} = array(
            'type' => $type,
            'req'  => $req,
            'def'  => $def,
            'filter'=>$filter
        );
        return $this;
    }

    public function addArgs(array $args) {
        foreach($args as $name => $arr) {
            $this->addArg($name,
                isset($arr['type'])? $arr['type']: "string",
                isset($arr['req'])?  $arr['req']: false,
                isset($arr['def'])?  $arr['def']: null,
                isset($arr['filter'])? $arr['filter']: false
            );
        }
        return $this;
    }

    public function addChild($path, $name, $type="string", $req=false, $def=null, $filter=false) {
        $p = $this->_getActualPath($path);
        if (!$this->data->exists("args>$p")){
            throw new MallocworksException("must define argument $path first before adding its child");
        }
        if ('array' != $this->getType($p)){
            throw new MallocworksException("type of path $path must be of type 'array' to have children");
        }
        if ($this->data->exists("args>$p>children>$name")){
            throw new MallocworksException("cannot redeclare argument $name");
        }
        $this->data->{"args>$p>children>$name"} = array(
            'type' => $type,
            'req'  => $req,
            'def'  => $def,
            'filter'=>$filter
        );
        return $this;
    }

    public function addChildren($path, array $args) {
        $p = $this->_getActualPath($path);
        foreach($args as $name => $arr) {
            $this->addChild($p, $name,
                isset($arr['type'])? $arr['type']: "string",
                isset($arr['req'])?  $arr['req']: false,
                isset($arr['def'])?  $arr['def']: null,
                isset($arr['filter'])? $arr['filter']: false
            );
        }
        return $this;
    }

    public function deleteArg($path) {
        $p = $this->_getActualPath($path);
        $this->data->del("args>$p");
        return $this;
    }

    public function setType($path, $type) {
        $p = $this->_getActualPath($path);
        $this->data->{"args>$p>type"} = $type;
        return $this;
    }

    public function setReq($path, $req) {
        $p = $this->_getActualPath($path);
        $this->data->{"args>$p>req"} = $req;
        return $this;
    }

    public function setDef($path, $def) {
        $p = $this->_getActualPath($path);
        $this->data->{"args>$p>def"} = $def;
        return $this;
    }

    public function setFilter($path, $filter) {
        $p = $this->_getActualPath($path);
        $this->data->{"args>$p>filter"} = $filter;
        return $this;
    }

    public function getType($path) {
        $p = $this->_getActualPath($path);
        return $this->data->get("args>$p>type", "string");
    }

    public function getReq($path) {
        $p = $this->_getActualPath($path);
        return $this->data->get("args>$p>req", false);
    }

    public function getDef($path) {
        $p = $this->_getActualPath($path);
        return $this->data->get("args>$p>def", null);
    }

    public function getFilter($path) {
        $p = $this->_getActualPath($path);
        return $this->data->get("args>$p>filter", false);
    }

    public function getArgs($path="") {
        if (empty($path)){
            return $this->data->keys('args');
        } else {
            $p = $this->_getActualPath($path);
            if ($replace == $path) {
                Logger::summon()->log("path '$path' should be of format arg_name>child_name", ComponentRoot::LEVEL_WARNING);
            }
            return $this->data->keys("args>$p");
        }
    }

    public function getChildren($path) {
        $p = $this->_getActualPath($path);
        return $this->data->keys("args>$p>children");
    }

    protected function _getActualPath($path) {
        $replace = str_replace(">", ">children>", $path);
        return $replace;
    }

    /**
     * get a list of html tags by ValidatorObject
     *
     * @param $prefix string putting in front as identifer
     * @param $basepath for resursive use only
     */
    public function toHtmlTags($prefix='', $basepath=''){
        $htmltags = array();

        $args = empty($basepath)
            ? $this->getArgs($basepath)
            : $this->getChildren($basepath);

        foreach($args as $arg) {
            $children = $this->getChildren($arg);
            $path = empty($basepath)
            	? $arg
            	: "$basepath>$arg";

            if (!empty($children)){
                //get html tags recursively
                $htmltags[$arg] = $this->toHtmlTags($prefix, $path);
            } else {
                //get html tag for this
                $req = $this->getReq($path);
                $def = $this->getDef($path);
                $field_path = empty($prefix)
                    ? $path
                    : "$prefix>$path";
                $field_name = false === strpos($field_path, ">")
                    ? $path
                    : str_replace(">", "][", preg_replace("/>/", "[", $field_path, 1)) . ']';
                $field_id = str_replace(">", "_", $field_path);

                $type = $this->getType($path);
                if (is_array($type)){
                    $htmltags[$arg] = "<select id=\"{$field_id}\" name=\"{$field_name}\">";
                    foreach($type as $t) {
                        $htmltags[$arg] .= "\n\t<option value=\"$t\">$t</option>";
                    }
                    $htmltags[$arg] .= "\n</select>";
                } else {
                    switch($this->getType($path)) {
                        case 'string':
                        case 'text':
                            $htmltags[$arg] = "<input type=\"text\" id=\"{$field_id}\" name=\"{$field_name}\" value=\"{$def}\"/>";
                            break;
                        case 'int':
                        case 'integer':
                        case 'float':
                        case 'double':
                            $htmltags[$arg] = "<input type=\"text\" id=\"{$field_id}\" name=\"{$field_name}\" value=\"{$def}\"/>";
                            break;
                        case 'bool':
                        case 'boolean':
                            $htmltags[$arg] = "<select id=\"{$field_id}\" name=\"{$field_name}\">\n\t<option value=\"1\">TRUE</option>\n\t<option value=\"0\">FALSE</option>\n</select>";
                            break;
                        case 'datetime':
                            $htmltags[$arg] = "<input type=\"text\" id=\"{$field_id}\" name=\"{$field_name}\" value=\"{$def}\"/>";
                            break;
                        case 'array':
                            $htmltags[$arg] = "";
                            break;
                        default:
                            $htmltags[$arg] = "<input type=\"text\" id=\"{$field_id}\" name=\"{$field_name}\" value=\"{$def}\"/>";
                    }
                }
            }
        }
        return $htmltags;
    }
}