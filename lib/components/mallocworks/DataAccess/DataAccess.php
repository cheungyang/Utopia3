<?php
namespace Utopia\Components\DataAccess;

use Utopia\Components\Core\MallocworksException;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;

class DataAccess extends ComponentRoot
{
	protected $_datasource = null;		//name of the datasource
	protected $_dsobj = null;			//the actual datasouce obj

	static public function isSingleton(){
		return false;
	}

	public function initialize($mixed=false){
		$this->_datasource = $mixed;
        ConfigurationBundle::summon()->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF);
	}

	public function begin(){
		$this->connect();
		$this->_dsobj->begin();
		return $this;
	}

	public function commit(){
		$this->_dsobj->commit();
		return $this;
	}

	public function rollback(){
		$this->_dsobj->rollback();
		return $this;
	}

	public function insert($entity, array $inputs) {
		$this->connect();
		return $this->_dsobj->insert($entity, $inputs);
	}

	public function update($entity, array $criteria, array $updates, $update_count=1) {
		$this->connect();
		return $this->_dsobj->update($entity, $criteria, $updates, $update_count);
	}

	public function delete($entity, array $criteria, $realdelete=false, $delete_count=1) {
		$this->connect();
		return $this->_dsobj->delete($entity, $criteria, $realdelete, $delete_count);
	}

	public function fetch(DataQuery $q) {
		$this->connect();
		return $this->_dsobj->fetch($q);
	}

	protected function connect(){
		if (is_null($this->_dsobj)){
			//determine datasource class
	        $class = ConfigurationBundle::summon()->{"dataaccess>datasources>".$this->_datasource};

			if (is_null($class)) {
				throw MallocworksException("datasource '{$this->_datasource}' undefined");
			}
	        if (!class_exists($class)){
	        	throw MallocworksException("class '$class' cannot be find");
	        }

        	$this->_dsobj = $class::summon();
		}
		return $this;
	}

	protected function disconnect(){
		if (!is_null($this->_dsobj)){
			$this->_dsobj->disconnect();
			unset($this->_dsobj);
		}
		return $this;
	}
}