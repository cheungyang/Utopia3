<?php
namespace Utopia\Components\DataModel;

use Utopia\Components\DataAccess\MysqliQuery;
use Utopia\Components\DataAccess\DataQuery;
use Utopia\Components\Validator\Validator;
use Utopia\Components\Validator\ValidationObject;
use Utopia\Components\ConfigurationBundle\ConfigurationBundle;
use Utopia\Components\DataAccess\DataAccess;
use Utopia\Components\Core\MallocworksException;
use Utopia\Components\Core\ComponentRoot;
use Utopia\Components\Core\DataObject;
use Utopia\Components\Logger\Logger;

class DataModel extends ComponentRoot
{
    static protected $_das=null;       //universal array of DataAccess objects
    static protected $_history=null;   //universal DataObject history
    protected $_entity=null;   //string, entity name
    protected $_schema=null;   //Schema object
    protected $_dbtable=false; //string, db table name
    protected $_dbtype =false; //string, db type


    static public function isSingleton(){
        return true;
    }

    public function initialize($mixed=false) {
        if (false == $mixed){
            throw new MallocworksException('please specify an entity name upon summoning a datamodel');
        }

        $this->_entity = $mixed;
        $schema_files = ConfigurationBundle::summon()
            ->setMasterFile(dirname(__FILE__).'/'.ComponentRoot::MASTER_CONF)
            ->{'datamodel.schemas'};
        $this->_schema = new DataSchema($schema_files[0]);

        //basic schema checks
        $this->_dbtable = $this->_schema->getTableName($this->_entity);
        if (!$this->_dbtable){
            throw new MallocworksException("cannot determine db table for entity '$mixed', schema error or typo?");
        }
        $this->_dbtype = $this->_schema->getDatabase($this->_entity);
        if (!$this->_dbtype){
            throw new MallocworksException("cannot determine db type for entity '$mixed', schema error or typo?");
        }

        //shared object creation
        if (!isset(self::$_das[$this->_dbtype])) {
            self::$_das[$this->_dbtype] = DataAccess::summon($this->_dbtype);
        }
        if (!isset(self::$_history)) {
            self::$_history = new DataObject();
        }
    }

    public function begin(){
        if (isset(self::$_das[$this->_dbtype])) {
            self::$_das[$this->_dbtype]->begin();
        }
    }

    public function commit(){
        if (isset(self::$_das[$this->_dbtype])) {
            self::$_das[$this->_dbtype]->commit();
        }
    }

    public function rollback(){
        if (isset(self::$_das[$this->_dbtype])) {
            self::$_das[$this->_dbtype]->rollback();
        }
    }

    public function insert($inputs){
        //setup valiadation
        $vo = new ValidationObject();
        $vo->fromSchema($this->_schema, $this->_entity)
           ->deleteArg('id');

        //add additional parameters
        $inputs['created_at'] = "now";
        $inputs['modified_at'] = "now";
        $inputs['loc'] = ConfigurationBundle::summon()->{'global>loc'};

        //validation
        $validator = Validator::summon();
        if (ComponentRoot::OKAY === $validator->validate($vo, $inputs)){
            $filtered = $validator->getFiltered();
            $rtn = self::$_das[$this->_dbtype]->insert($this->_dbtable, $filtered);
            $this->_saveHistory('insert', $inputs, $rtn);
            if (ComponentRoot::ERROR === $rtn){
                Logger::summon()->log('failed inserting item', ComponentRoot::LEVEL_ERROR);
            }
        } else {
            $this->_saveHistory('insert', $inputs, ComponentRoot::ERROR_VALIDATION);
            Logger::summon()->log('validation failed, insert operation failed', ComponentRoot::LEVEL_ERROR);
        }
        return $this;
    }

    public function update($id, $updates){
        //setup valiadation
        $vo = new ValidationObject();
        $vo->fromSchema($this->_schema, $this->_entity);
        foreach($vo->getArgs() as $arg) {
            $vo->setDef($arg, null)
               ->setReq($arg, false);
        }

        //add additional parameters
        $updates['modified_at'] = "now";

        //validation
        $validator = Validator::summon();
        if (ComponentRoot::OKAY === $validator->validate($vo, $updates)){
            $filtered = $validator->getFiltered();
            $rtn = self::$_das[$this->_dbtype]->update($this->_dbtable, array('id'=>$id), $filtered);
            $this->_saveHistory('update', $updates, $rtn);

            if (ComponentRoot::ERROR === $rtn){
                Logger::summon()->log('failed updating item', ComponentRoot::LEVEL_ERROR);
            }
        } else {
            $this->_saveHistory('update', $updates, ComponentRoot::ERROR_VALIDATION);
            Logger::summon()->log('validation failed, update operation failed', ComponentRoot::LEVEL_ERROR);
        }

        return $this;
    }

    public function delete($id, $realdelete=false){
        if (true === $realdelete) {
            $rtn = self::$_das[$this->_dbtype]->delete($this->_dbtable, array('id'=>$id), true, 1);
            $this->_saveHistory('delete', array('id'=>$id), $rtn);
            //error handling
            if (ComponentRoot::ERROR === $rtn){
                Logger::summon()->log('failed real deleting item', ComponentRoot::LEVEL_ERROR);
            }
        } else {
            //flag delete is marking is_delete = 1
            $updates = array(
            	'modified_at' => "now",
                'is_delete'   => 1,
            );
            return $this->update($id, $updates);
        }

        return $this;
    }

    public function fetch($q){
        if ($q instanceof DataQuery) {
            return $this->fetchByQuery($q);
        } elseif (!is_array($q)) {
            return $this->fetchById($q);
        }

        //error handling
        $this->_saveHistory('fetch', $q, ComponentRoot::ERROR_VALIDATION);
        Logger::summon()->log('failed fetching with strange data type', ComponentRoot::LEVEL_ERROR);
        return $this;
    }

    /**
     * handling fetchByXX calls
     *
     * @param $name      fetchByXX
     * @param $arguments array of arguments
     */
    public function __call($name, $arguments) {
        if (0 != strcmp(mb_substr($name,0,7),'fetchBy')) {
            throw new MallocworksException("function $name is not defined");
        }

        //formulate DataQuery object
        $target = mb_strtolower(mb_substr($name,7));
        switch($target) {
            case 'query':
                if (!isset($arguments[0]) || !$arguments[0] instanceof DataQuery){
                    Logger::summon()->log('calling fetchByQuery should apply with a DataQuery class as input', ComponentRoot::LEVEL_ERROR);
                    return ComponentRoot::ERROR;
                }
                $q = $arguments[0];
                break;
            case 'id':
                //set limit to 1
                if (!isset($arguments[0]) || empty($arguments[0])){
                    Logger::summon()->log("calling fetchBy".ucfirst($target)." should apply with an argument", ComponentRoot::LEVEL_ERROR);
                    return ComponentRoot::ERROR;
                }
                $where = array($target=>$arguments[0]);
                //FIXME: should use DataQuery
                $q = MysqliQuery::select()->where($where)->limit(1);
                break;
            default:
                //general case
                if (!isset($arguments[0]) || empty($arguments[0])){
                    Logger::summon()->log("calling fetchBy".ucfirst($target)." should apply with an argument", ComponentRoot::LEVEL_ERROR);
                    return ComponentRoot::ERROR;
                }
                $where = array($target=>$arguments[0]);
                //FIXME: should use DataQuery
                $q = MysqliQuery::select()->where($where);
        }

        //making sure parameters are correct
        $q->from($this->_dbtable);

        //execute the query
        $rtn = self::$_das[$this->_dbtype]->fetch($q);
        $this->_saveHistory('fetch', $q, $rtn);
        if (ComponentRoot::ERROR === $rtn){
            Logger::summon()->log('failed fetching from query '.$q->getSql(), ComponentRoot::LEVEL_ERROR);
        }

        return $this;
    }

    public function getLatestData($path='', $default=null){
        return empty($path)
            ? self::$_history->get('history>0>outputs', $default)
            : self::$_history->get("history>0>outputs>$path", $default);
    }

    public function getLatestStatus(){
        return self::$_history->get('history>0>status', ComponentRoot::ERROR);
    }

    public function getLatestOperation($path='', $default=null){
        return empty($path)
            ? self::$_history->get('history>0', $default)
            : self::$_history->get("history>0>$path", $default);
    }

    protected function _saveHistory($op, $in, $out){
        self::$_history->unshift('history', array(
            'dbtype'	=> $this->_dbtype,
            'dbtable'	=> $this->_dbtable,
            'operation' => $op,
            'status' 	=> is_array($out)? ComponentRoot::OKAY: $out,
            'inputs'	=> $in,
            'outputs'	=> ComponentRoot::OKAY !== $out && !is_array($out)? false: $out
        ));
        return $this;
    }
}