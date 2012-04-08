<?php
namespace Utopia\Components\DataModel;

use Utopia\Components\Core\DataObject;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\DataParser\DataParser;

class DataSchema
{
    private $_schema; //DataObject

    public function __construct($schema_source) {
        $this->_schema = new DataObject();
        $this->_schema->set('source', DataParser::summon()->asArray($schema_source));
    }

    public function getSchema($entityname) {
        //return if already done
        if (!$this->_schema->isempty('doctrine>'.$entityname)){
            return $this->_schema->{'doctrine>'.$entityname};
        }

        //check if entity exists
        if (!isset($this->_schema->{"source>schema>$entityname"})) {
            throw new MallocworksException("schema for '$entityname' does not exist");
        }

        //parse fields and relationships
        $types = $this->_schema->{"source>schema>$entityname>type"};
        if (!is_array($types)) {
            $types = array($types);
        }
        $fields = $this->_schema->get("source>schema>$entityname>fields", array());
        $relationships = $this->_schema->get("source>schema>$entityname>relationships", array());
        foreach($types as $type) {
            $fields = array_merge_recursive($fields, $this->_schema->get("source>schema>types>$type>fields", array()));
            $relationships = array_merge_recursive($relationships, $this->_schema->get("source>schema>types>$type>relationships", array()));
        }

        //make a doctrine schema
        $this->_schema->{'doctrine>'.$entityname} = array('fields'=>$fields, 'relationships'=>$relationships);
        return $this->_schema->{'doctrine>'.$entityname};
    }

    public function getSchemaOptions() {
        return $this->_schema->get("source>schema>options", array());
    }

    public function getTableName($entityname) {
        return $this->_schema->{"source>schema>$entityname>table"};
    }

    public function getDatabase($entityname) {
        return $this->_schema->{"source>schema>$entityname>database"};
    }

    public function getEntityNames() {
		$filters = explode(
			',',
		    ConfigurationBundle::summon()->get_value('datamodel.schema>schema_ingore_keys', 'types,options')
	    );
        return array_values(array_diff(
            $this->_schema->keys('source>schema', array()),
            $filters
        ));
    }

    public function is_loc($entityname) {
        return $this->_schema->in("source>schema>$entityname>type", 'translateable');
    }

    public function is_seq($entityname) {
        return $this->_schema->in("source>schema>$entityname>type", 'chainable');
    }
}