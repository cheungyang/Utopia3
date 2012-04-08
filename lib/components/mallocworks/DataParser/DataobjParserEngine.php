<?php
namespace Utopia\Components\DataParser;

use Utopia\Components\Core\DataObject;
use Utopia\Components\DataParser\IParserEngine;

class DataobjParserEngine implements IParserEngine {
    public function __construct() {

    }

    public function getType() {
        return Parser::TYPE_DATAOBJ;
    }

    public function acceptExtract($input) {
        return $input instanceof DataObject? true: false;
    }

    public function acceptPack($data) {
        if (is_array($data)) {
            return true;
        } else {
            return false;
        }
    }

    public function extract($input, $args=array()) {
        return $input->get('');
    }

    public function pack($data) {
        return new DataObject($data);
    }
}