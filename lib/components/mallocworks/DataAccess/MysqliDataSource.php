<?php
namespace Utopia\Components\DataAccess;

use Utopia\Components\Logger\Logger;

use Utopia\Components\Core\MallocworksException;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\DataAccess\MysqliQuery;
use Utopia\Components\DataAccess\IDataSource;

class MysqliDataSource extends ComponentRoot implements IDataSource
{
    protected $_conn, $_stmt;  //connection and execution statement
    protected $_config;        //config array

    static function isSingleton(){
        return false;
    }

    public function initialize($mixed=false){
        $this->_config = ConfigurationBundle::summon()
            ->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF)
            ->{'dataaccess.mysqli.config'};
    }

    public function __construct() {
    }

    public function __destruct() {
        //close transaction if it is not closed
        $this->disconnect();
    }

    /**
     * establish persistent datasource connection
     *
     * @return connection object
     */
    public function connect() {
        if (!is_null($this->_conn)){
            return $this;
        }

        try {
            $mysqli = new \mysqli(
                $this->_config['host'],
                $this->_config['user'],
                $this->_config['password'],
                $this->_config['dbname'],
                $this->_config['port']
            );
        } catch(\Exception $e) {
            throw new MallocworksException('connect error, '. $e->getMessage());
        }

        if ($mysqli->connect_error) {
            throw new MallocworksException('connect error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
        }

        //utf8 settings, obviously
        $mysqli->query("SET NAMES 'utf8'");
        $mysqli->query("SET CHARACTER SET 'utf8'");

        $this->_conn = $mysqli;
        $this->_conn->autocommit(true);
        return $this;
    }

    /**
     * disconnect a connection
     *
     * @return true
     */
    public function disconnect() {
        if (isset($this->_conn)) {
            $this->_conn->close();
            unset($this->_conn);
        }
        return $this;
    }

    /**
     * start a datasource transaction
     *
     * @return self
     */
    public function begin() {
        $this->connect();
        $this->_conn->autocommit(false);
        return $this;
    }

    /**
     * commit a datasource transaction
     *
     * @return self
     */
    public function commit() {
        $this->connect();
        $this->_conn->commit();
        $this->_conn->autocommit(true);
        return $this;
    }

    /**
     * rollback a datasource transaction
     *
     * @return self
     */
    public function rollback() {
        $this->connect();
        $this->_conn->rollback();
        $this->_conn->autocommit(true);
        return $this;
    }

	public function insert($entity, array $inputs){
	    $q = MysqliQuery::insert($entity)->values($inputs);
	    $affected_rows = $this->_execute($q);

	    //get inserted data
	    if ($affected_rows == 1){
            $new_id = $this->_stmt->insert_id;
            $field_id = ConfigurationBundle::summon()->{'dataaccess.fields>id'};
            $q = MysqliQuery::select()->from($entity)->where($field_id, $new_id);
            return $this->fetch($q);
	    } else {
	        Logger::summon()->log("rows affected is $affected_rows, which is different from expected '1'", ComponentRoot::LEVEL_ERROR);
	        return ComponentRoot::ERROR;
	    }
	}

	public function update($entity, array $criteria, array $updates, $update_count=1){
	    //check if the update count = affected rows
	    $q = MysqliQuery::select('count(*) as count')->from($entity)->where($criteria);
	    $rtn = $this->fetch($q);
	    if ($update_count != $rtn[0]['count']){
	        Logger::summon()->log("rows to be affected is {$rtn[0]['count']}, which is different from expected $update_count", ComponentRoot::LEVEL_ERROR);
	        return ComponentRoot::ERROR;
	    }

	    //perform the update
	    $q = MysqliQuery::update($entity)->set($updates)->where($criteria);
	    $affected_rows = $this->_execute($q);

	    //get updated results
        if ($affected_rows == $update_count){
            $q = MysqliQuery::select()->from($entity)->where($criteria);
            return $this->fetch($q);
	    } else {
	        Logger::summon()->log("modified rows is $affected_rows, which is different from expected $update_count", ComponentRoot::LEVEL_ERROR);
            return ComponentRoot::ERROR;
	    }
	}

	public function delete($entity, array $criteria, $realdelete=false, $delete_count=1){
	    //check if the update count = affected rows
	    $q = MysqliQuery::select('count(*) as count')->from($entity)->where($criteria);
	    $rtn = $this->fetch($q);
	    if ($delete_count != $rtn[0]['count']){
	        Logger::summon()->log("rows to be deleted is {$rtn[0]['count']}, which is different from expected $delete_count", ComponentRoot::LEVEL_ERROR);
	        return ComponentRoot::ERROR;
	    }

	    //perform deletion
	    if ($realdelete) {
	        //get the snapshot before deletion
	        $q = MysqliQuery::select()->from($entity)->where($criteria);
	        $deleted_data = $this->fetch($q);

	        //delete
	        $q = MysqliQuery::delete($entity)->where($criteria);
	        $affected_rows = $this->_execute($q);
	        if ($affected_rows == $delete_count) {
	            return $deleted_data;
	        } else {
                Logger::summon()->log("deleted rows is $affected_rows, which is different from expected $delete_count", ComponentRoot::LEVEL_ERROR);
                return ComponentRoot::ERROR;
	        }
	    } else {
	        //update delete field
	        $field_delete = ConfigurationBundle::summon()->{'dataaccess.fields>delete'};
	        $q = MysqliQuery::update($entity)->set($field_delete, 1)->where($criteria);
	        $affected_rows = $this->_execute($q);

	        //check if "deletion" succeed
	        if ($affected_rows == $delete_count) {
	            $q = MysqliQuery::select()->from($entity)->where($criteria)->where($field_delete, 1);
	            return $this->fetch($q);
	        } else {
	            Logger::summon()->log("deleted rows is $affected_rows, which is different from expected $delete_count", ComponentRoot::LEVEL_ERROR);
	            return ComponentRoot::ERROR;
	        }
	    }
	    $affected_rows = $this->_execute($q);
	}

	public function fetch(DataQuery $q){
	    if ($q->getOperation() != "select") {
	        Logger::summon()->log("query passed into fetch call is not a select opreation", ComponentRoot::LEVEL_ERROR);
            return ComponentRoot::ERROR;
	    }
	    return $this->_execute($q);
	}

    /**
     * execute a mysql query
     *
     * @param MysqliQuery $q
     */
    protected function _execute(MysqliQuery $q) {
        //warm up
        $this->_reset()->connect();

        //prepare sql statement
        $sql = $q->getSql();
        if (($this->_stmt = $this->_conn->prepare($sql))== false){
            Logger::summon()->log('error in stmt: ('.$this->_conn->errno.') '. $this->_conn->error, ComponentRoot::LEVEL_ERROR);
            return ComponentRoot::ERROR;
        }

        $params = $q->getStmtIdx($q->getParams());
        //print_r(array($q->getSql(), $params));

        //no param, no binding is needed
        if (!empty($params[0])) {
            call_user_func_array(array($this->_stmt, 'bind_param'), $this->_refValues($params));
        }

        //run the queryxml
        if (!$this->_stmt->execute()){
            //statement failed
            Logger::summon()->log('error in mysqli: ('.$this->_stmt->errno.') '. $this->_stmt->error, ComponentRoot::LEVEL_ERROR);
            return ComponentRoot::ERROR;
        }

        //return by query operation
        switch ($q->getOperation()) {
            case 'select': return $this->_getData(); break;
            case 'update':
            case 'delete':
            case 'insert': return $this->_stmt->affected_rows; break;
            default: return false;
        }
    }

    /**
     * void statment object
     *
     */
    protected function _reset() {
        //close down
        if (isset($this->_stmt) && is_object($this->_stmt)) {
            try {
                $this->_stmt->free_result();
                $this->_stmt->close();
            } catch(\Exception $e) {
                Logger::summon()->log("datasource reset error, ".$e->getMessage(), ComponentRoot::LEVEL_ERROR);
                return ComponentRoot::ERROR;
            }
            unset($this->_stmt);
        }

        return $this;
    }

    /**
     * return data from statement
     *
     */
    protected function _getData() {
        $meta = $this->_stmt->result_metadata();
        if (!$meta) {
            return array();
        }

        while ($field = $meta->fetch_field()) {
            $fields[] = &$row[$field->name];
        }
        $meta->close();

        $this->_stmt->store_result();

        $result = array();
        call_user_func_array(array($this->_stmt, 'bind_result'), $fields);
        while ($this->_stmt->fetch()) {
           foreach($row as $key => $val) {
                $c[$key] = $val;
            }
            $result[] = $c;
        }
        return $result;
    }

    /**
     * ???
	 *
     * @param array $arr
     */
    protected function _refValues($arr) {
        if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
        {
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }
}