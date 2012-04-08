<?php
namespace Utopia\Components\DataModel;

abstract class BaseModel
{
    const OKAY = 200;
    const DUPLICATED = 201;
    const ERROR = -1;
    const NOTAPPLICABLE = -2;
    const VALIDATION = -10;

    private $_dso;              //DSO
    protected $sc;              //ServiceContainer class
    protected $autoload;        //Autoload
    protected $entity_name;     //string
    protected $history;         //DataObj
    protected $cache;           //DataObj

    /**
     * constructor
     *
     * @param ServiceContainer $sc
     * @param Autoload         $autoload
     */
    public function __construct(ServiceContainer $sc, Autoload $autoload, $entityname) {
        $this->sc = $sc;
        $this->autoload = $autoload;
        $this->entity_name = $entityname;
        $this->history = new DataObject();
        $this->_dso = $this->sc->mysqli;
        //load cache
        $this->cache = new DataObject('model.'.$entityname);
        $this->cache->load($this->sc->getParameter('model.cache'));
    }

    public function getName() {
        return $this->entity_name;
    }

    public function getTableName() {
        return $this->sc->schema->getTableName($this->getName());
    }

    public function getLatestData($format='array') {
        if ($format=='array') {
            return $this->history->get('operations'.SEP.'0'.SEP.'data', array());
        } else {
            return $this->sc->parser->{'as'.ucfirst($format)}($this->history->get('operations'.SEP.'0'.SEP.'data', array()));
        }
    }

    //============OPERATIONS================

    /**
     * create a new entity
     *
     * @param array $inputs
     */
    public function createNew(array $inputs) {
        //must add
        $inputs = array_merge(array(
            'ver'           => 1,
            'seq'           => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'modified_at'   => date('Y-m-d H:i:s'),
            'entity_id'     => $this->sc->helper->util_generateId(),   //random number for unique id
        ), $inputs);

        //data validation
        $filtered = $this->validateInputs($inputs, false, array('id'));
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //mysql execution
        $q = MysqliQuery::insert($this->getTableName())
            ->values($filtered)
            ->duplicatekey($this->getUpdateKeys($filtered, array_merge($this->sc->schema->getIdentifierKeys(), array('created_at'))));
//        echo $q->getSql();
//        print_r($q->getParams());
        $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);

        //query execution
        try {
            $records = $this->_dso->execute($q);
        } catch (ModelException $e) {
            $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //history saving
        $this->history->unshift('operations', array(
            'method'=>__METHOD__,
            'data'=>$filtered
        ));

        //return
        if ($records == 1) {
            return BaseModel::OKAY;
        } else {
            return BaseModel::DUPLICATED;
        }
    }

    /**
     * create a new version of an existing entity
     *
     * @param array $criteria
     * @param array $inputs
     */
    public function createVer(array $criteria, array $inputs) {
        //return if no versioning
        if (!$this->sc->schema->is_ver($this->getName())) {
            return BaseModel::NOTAPPLICABLE;
        }

        //validate criteria
        $filtered = $this->validateCriteria($criteria, array('ver'));
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //get latest version, extra data adding
        $inputs = array_merge($inputs, $filtered, array(
            'seq'           => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'modified_at'   => date('Y-m-d H:i:s'),
        	'ver'           => $this->getLatestVer($filtered)+1,
        ));


        //data validation
        $filtered = $this->validateInputs($inputs, false, array('id'));
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //mysql execution
        $q = MysqliQuery::insert($this->getTableName())
            ->values($filtered)
            ->duplicatekey($this->getUpdateKeys($filtered, array_merge($this->sc->schema->getIdentifierKeys(), array('created_at'))));
//        echo $q->getSql();
//        print_r($q->getParams());
        $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);

        //query execution
        try {
            $records = $this->_dso->execute($q);
        } catch (ModelException $e) {
            $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //history saving
        if ($records == 1) {
            $this->history->unshift('operations', array(
                'method'=>__METHOD__,
                'data'=>$filtered
            ));
            return BaseModel::OKAY;
        } else {
            return BaseModel::DUPLICATED;
        }
    }

    /**
     * create a new sequence of an existing entity
     *
     * @param array $criteria
     * @param array $inputs
     */
    public function createSeq(array $criteria, array $inputs) {
        //return if not chainable
        if (!$this->sc->schema->is_seq($this->getName())) {
            return BaseModel::NOTAPPLICABLE;
        }

        //validate criteria
        $filtered = $this->validateCriteria($criteria, array('seq','ver'));
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //get latest version, extra data adding
        $inputs = array_merge($inputs, $filtered, array(
            'ver'           => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'modified_at'   => date('Y-m-d H:i:s'),
        	'seq'           => $this->getLatestSeq($filtered)+1,
        ));

        //data validation
        $filtered = $this->validateInputs($inputs, false, array('id'));
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //mysql execution
        $q = MysqliQuery::insert($this->getTableName())
            ->values($filtered)
            ->duplicatekey($this->getUpdateKeys($filtered, array_merge($this->sc->schema->getIdentifierKeys(), array('created_at'))));
//        echo $q->getSql();
//        print_r($q->getParams());
        $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);

        //query execution
        try {
            $records = $this->_dso->execute($q);
        } catch (ModelException $e) {
            $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //history saving
        if ($records == 1) {
            $this->history->unshift('operations', array(
                'method'=>__METHOD__,
                'data'=>$filtered
            ));
            return BaseModel::OKAY;
        } else {
            return BaseModel::DUPLICATED;
        }
    }

    /**
     * create a new translation of an existing entity
     *
     * @param array $criteria
     * @param array $inputs
     */
    public function createLoc(array $criteria, array $inputs) {
        //return if no loc
        if (!$this->sc->schema->is_loc($this->getName())) {
            return BaseModel::NOTAPPLICABLE;
        }

        //validate criteria
        $filtered = $this->validateCriteria($criteria, array('loc','seq','ver'));
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //get latest version, extra data adding
        $inputs = array_merge($inputs, $filtered, array(
            'seq'           => 1,
        	'ver'           => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'modified_at'   => date('Y-m-d H:i:s'),
        ));

        //data validation
        $filtered = $this->validateInputs($inputs, false, array('id'));
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //mysql execution
        $q = MysqliQuery::insert($this->getTableName())
            ->values($filtered)
            ->duplicatekey($this->getUpdateKeys($filtered, array_merge($this->sc->schema->getIdentifierKeys(), array('created_at'))));
//        echo $q->getSql();
//        print_r($q->getParams());
        $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);

        //query execution
        try {
            $records = $this->_dso->execute($q);
        } catch (ModelException $e) {
            $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //history saving
        if ($records == 1) {
            $this->history->unshift('operations', array(
                'method'=>__METHOD__,
                'data'=>$filtered
            ));
            return BaseModel::OKAY;
        } else {
            return BaseModel::DUPLICATED;
        }
    }

    /**
     * update one row
     *
     * @param array $criteria
     * @param array $inputs
     */
    public function update(array $criteria, $inputs) {
        //validate criteria
        $criteria_filtered = $this->validateCriteria($criteria);
        if ($criteria_filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //get that record from db
        $q = MysqliQuery::select('*')
            ->from($this->getTableName())
            ->where($criteria_filtered);
        $records = $this->_dso->execute($q);
        $this->sc->logger->log($q->getSql(), Log::LEVEL_ERROR);
        if (count($records) != 1) {
            $this->sc->logger->log('found '.count($records).' using existing update criteria, expecting 1: '. serialize($criteria), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //unset criteria fields that cannot be batch processed
        //foreach($this->sc->schema->getIdentifierKeys() as $key) {
        foreach(array('id', 'entity_id', 'loc', 'seq', 'ver') as $key) {
            if (isset($records[0][$key])) {
                unset($records[0][$key]);
            }
        }

        //check if update is necessary
        if (array_diff_assoc($inputs, $records[0]) == array()) {
            $this->sc->logger->log('no data change, no update necessary: '. serialize($criteria), Log::LEVEL_DEBUG);
            $this->history->unshift('operations', array(
                'method'=>__METHOD__,
                'data'=>$records[0]
            ));
            return BaseModel::DUPLICATED;
        }

        //extra data adding
        $inputs = array_merge($records[0], $inputs, array(
            'modified_at' => date('Y-m-d H:i:s')
        ));

        //data validation
        $filtered = $this->validateInputs($inputs, true);
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //mysql execution
        $q = MysqliQuery::update($this->getTableName())
            ->set($filtered)
            ->where($criteria_filtered)
            ->limit(1);
//        echo $q->getSql();
//        print_r($q->getParams());
        $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);

        //query execution
        try {
            $records = $this->_dso->execute($q);
        } catch (ModelException $e) {
            $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //history saving
        if ($records == 1) {
            $this->history->unshift('operations', array(
                'method'=>__METHOD__,
                'data'=>$filtered
            ));
            return BaseModel::OKAY;
        } else {
            return BaseModel::DUPLICATED;
        }
    }

    /**
     * update the entire entity
     *
     * @param array $criteria
     * @param array $inputs
     */
    public function updateEntity(array $criteria, $inputs) {
        //validate criteria
        $criteria_filtered = $this->validateCriteria($criteria, array('loc','ver','seq'));
        if ($criteria_filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //get that record from db
        $q = MysqliQuery::select('*')
            ->from($this->getTableName())
            ->where($criteria_filtered);
        $records = $this->_dso->execute($q);

        //check if update is necessary, if yes, add into queue
        $changeids = array();
        foreach($records as &$record) {
            if (array_diff_assoc($inputs, $record) == array()) {
                continue;
            }
            $changeids[] = $record['id'];
        }
        unset($record);
        if (count($changeids) == 0) {
            $this->sc->logger->log("no data change for all ".count($records)." records, no update necessary", Log::LEVEL_DEBUG);
            $this->history->unshift('operations', array(
                'method'=>__METHOD__,
                'data'=>$records
            ));
            return BaseModel::OKAY;
        } else {
            $this->sc->logger->log("changing ".count($changeids)."/".count($records)." records", Log::LEVEL_DEBUG);
        }

        //extra data adding
        $inputs = array_merge($inputs, array(
            'modified_at' => date('Y-m-d H:i:s'),
        ));

        //data validation (set noreq to true)
        $filtered = $this->validateInputs($inputs, true);
        if ($filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //mysql execution
        $q = MysqliQuery::update($this->getTableName())
            ->set($filtered)
            ->limit(count($changeids));
        foreach($changeids as $id) {
            $q->orWhere('id', $id);
        }

//        echo $q->getSql();
//        print_r($q->getParams());
        $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);

        //query execution
        try {
            $rtn = $this->_dso->execute($q);
        } catch (ModelException $e) {
            $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //history saving
        if ($rtn == count($changeids)) {
            //modify content for execution
            foreach ($records as &$record){
                if (in_array($record['id'], $changeids)) {
                    $record = array_merge($record, $filtered);
                }
            }
            unset($record);

            //real history saving
            $this->history->unshift('operations', array(
                'method'=>__METHOD__,
                'data'=>$records
            ));
            return BaseModel::OKAY;
        } else {
            return BaseModel::DUPLICATED;
        }
    }

    /**
     * delete/mark delete one row
     *
     * @param array $criteria
     * @param bool  $realdelete
     */
    public function delete(array $criteria, $realdelete=false) {
        //validate criteria
        $criteria_filtered = $this->validateCriteria($criteria);
        if ($criteria_filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //get that record from db
        $q = MysqliQuery::select('*')
            ->from($this->getTableName())
            ->where($criteria_filtered);
//        echo $q->getSql();
//        print_r($q->getParams());
        $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);

        $records = $this->_dso->execute($q);
        if (count($records) != 1) {
            $this->sc->logger->log('found '.count($records).' using existing delete criteria, expecting 1', Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //delete handling by case
        if (!$realdelete){
            //mark delete case
            if ($records[0]['is_delete']) {
                $this->sc->logger->log('data already deleted, no update necessary', Log::LEVEL_DEBUG);
                $this->history->unshift('operations', array(
                    'method'=>__METHOD__,
                    'data'=>$records[0]
                ));
                return BaseModel::OKAY;
            } else {
                return $this->update($criteria_filtered, array(
                	'is_delete' => true,
                	'modified_at' => date('Y-m-d H:i:s'),
                ));
            }
        } else {
            //real delete case
             $q = MysqliQuery::delete($this->getTableName())
                ->where($criteria_filtered)
                ->limit(1);

            //query execution
            try {
                $records = $this->_dso->execute($q);
            } catch (ModelException $e) {
                $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
                return BaseModel::ERROR;
            }

            //history saving
            if ($records == 1) {
                $this->history->unshift('operations', array(
                    'method'=>__METHOD__,
                    'data'=>$records[0]
                ));
                return BaseModel::OKAY;
            } else {
                return BaseModel::ERROR;
            }
        }
    }

    /**
     * delete the entire entity
     *
     * @param array $criteria
     * @param bool  $realdelete
     */
    public function deleteEntity(array $criteria, $realdelete=false) {
        //validate criteria
        $criteria_filtered = $this->validateCriteria($criteria, array('loc','ver','seq'));
        if ($criteria_filtered == BaseModel::VALIDATION) {
            return BaseModel::VALIDATION;
        }

        //get that record from db
        $q = MysqliQuery::select('*')
            ->from($this->getTableName())
            ->where($criteria_filtered);
        $records = $this->_dso->execute($q);

        //delete handling by case
        if (!$realdelete){
            //mark delete case
            return $this->updateEntity($criteria_filtered, array(
             	'is_delete' => true,
            	'modified_at' => date('Y-m-d H:i:s'),
            ));
        } else {
            //real delete statement
            $this->sc->logger->log('real deleting '.count($records).' records', Log::LEVEL_DEBUG);
            $q = MysqliQuery::delete($this->getTableName())
                ->limit(count($records));
            foreach($records as $record) {
                $q->orWhere('id', $record['id']);
            }
//            echo $q->getSql();
//            print_r($q->getParams());
            $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);

            //query execution
            try {
                $rtn = $this->_dso->execute($q);
            } catch (ModelException $e) {
                $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
                return BaseModel::ERROR;
            }

            //history saving
            if ($rtn == count($records)) {
                $this->history->unshift('operations', array(
                    'method'=>__METHOD__,
                    'data'=>$records
                ));
                return BaseModel::OKAY;
            } else {
                return BaseModel::ERROR;
            }
        }
    }

    /**
     * fetch operation
     *
     * @param MysqliQuery $q
     * @param bool        $forcerun true to bypass cache
     *
     * @return mixed
     */
    public function fetch(MysqliQuery $q, $forcerun=false) {
        //check if request valid
        if ($q->getOperation() != 'select'){
            $this->sc->logger->log('ERROR: inputted MysqliQuery object is not a select statement', Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        //check if cache available
//        $cachehash = 'fetch'.SEP.md5($q->getSql()).SEP.md5(serialize($q->getParams()));
//
//        if (!$forcerun && $this->cache->exists($cachehash)) {
//            $cached = $this->cache->{$cachehash};
//            //only accept when cached data is not empty
//            if (isset($cached['data']) && !empty($cached['data'])){
//                $this->sc->logger->log("CACHED: ".$this->getName()." - ".$q->getSql(), Log::LEVEL_DEBUG);
//                $this->history->unshift('operations', $cached);
//                return BaseModel::OKAY;
//            }
//        }

        //execute query
        try {
            $this->sc->logger->log($q->getSql(), Log::LEVEL_DEBUG);
            $records = $this->_dso->execute($q);
        } catch (ModelException $e) {
            $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }
        $response = array(
            'method'=>__METHOD__,
            'data'=>$records
        );

        //record history
        $this->history->unshift('operations', $response);
        //save cache
//        $this->cache->{$cachehash} = $response;
//        $this->cache->save();
        return BaseModel::OKAY;
    }

    /**
     * execute whatever the query is, too dangerous?
     *
     * @param MysqliQuery $q
     *
     * @return mixed
     */
    public function rawexecute(MysqliQuery $q) {
        try {
            $records = $this->_dso->execute($q);
        } catch (ModelException $e) {
            $this->sc->logger->log($e->getMessage(), Log::LEVEL_ERROR);
            return BaseModel::ERROR;
        }

        $this->history->unshift('operations', array(
            'method'=>__METHOD__,
            'data'=>$records
        ));
        return BaseModel::OKAY;
    }


    public function map(array $criteria, $mapinfo) {

    }

    public function unmap(array $criteria, $mapinfo) {

    }

    public function fetchmaps(array $criteria) {

    }

    protected function getLastId() {
        $q = MysqliQuery::select('max(`id`) as max')
            ->from($this->getTableName());
        $rtn = $this->_dso->execute($q);
        return $rtn[0]['max'];
    }

    protected function getLatestVer(array $criteria) {
        $q = MysqliQuery::select('max(`ver`) as max')
            ->from($this->getTableName())
            ->where($criteria);
        $rtn = $this->_dso->execute($q);
        return $rtn[0]['max'];
    }

    protected function getLatestSeq(array $criteria) {
        $q = MysqliQuery::select('max(`seq`) as max')
            ->from($this->getTableName())
            ->where($criteria);
        $rtn = $this->_dso->execute($q);
        return $rtn[0]['max'];
    }

    protected function getAllLoc($criteria) {
        $q = MysqliQuery::select('loc')
            ->from($this->getTableName())
            ->where($criteria);
        $rtn = $this->_dso->execute($q);
        $locs = array();
        foreach($rtn as $r) {
            $locs[] = $r['loc'];
        }
        return $locs;
    }

    protected function validateInputs(array $inputs, $noreq=false, $whitelist=array()) {
        $spec = $this->sc->schema->getValidationSpec($this->getName());

        //set all required fields to false
        if ($noreq) {
            foreach ($spec as &$s){
                if (isset($s['req'])) {
                    unset($s['req']);
                }
            }
            unset($s);
        }

        //remove spec fields in whitelist
        foreach($whitelist as $w) {
            if (isset($spec[$w])) {
                unset($spec[$w]);
            }
        }

        //perform validation
        $validator = $this->sc->validator;
        if (!$validator->setSpec($spec)->validate($inputs)->isSuccess()){
            return BaseModel::VALIDATION;
        } else {
            return $validator->getFiltered();
        }
    }

    protected function validateCriteria(array $criteria, array $exclude=array()) {
        $criteriaspec = $this->sc->schema->getCriteriaSpec($this->getName(), $exclude);
        $validator = $this->sc->validator->setSpec($criteriaspec);
        if (!$validator->validate($criteria)->isSuccess()){
            return BaseModel::VALIDATION;
        } else {
            return $validator->getFiltered();
        }
    }

    protected function getUpdateKeys(array $inputs, array $excludes=array()){
        $duplicate_update = $inputs;
        foreach($excludes as $e){
            if (isset($duplicate_update[$e])) {
                unset($duplicate_update[$e]);
            }
        }
        return array_keys($duplicate_update);
    }
}