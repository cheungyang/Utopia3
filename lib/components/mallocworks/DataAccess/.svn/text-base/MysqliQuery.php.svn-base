<?php
namespace Utopia\Components\DataAccess;

use Utopia\Components\DataAccess\DataQuery;

class MysqliQuery extends DataQuery
{
    private $_p, $_op, $_dirty;

    private $_params; //for prepare statement parameters
    private $_query;  //for prepare statement query

    /**
     * entry for select statements
     *
     * @param array|string field(s) to select
     */
    static public function select($mixed='*')
    {
        $i = new self('select');
        if (is_array($mixed)){
            $i->_p['select'] = $mixed;
        } else {
            $i->_p['select'][] = $mixed;
        }
        return $i;
    }

    /**
     * entry to insert statements
     *
     * @param string $table
     */
    static public function insert($table) {
        $i = new self('insert');
        $i->_p['from'] = $table;
        return $i;
    }

    /**
     * entry for delete statements
     *
     * @param string $table
     */
    static public function delete($table) {
        $i = new self('delete');
        $i->_p['from'] = $table;
        return $i;
    }

    /**
     * entry for update statements
     *
     * @param string $table
     */
    static public function update($table) {
        $i = new self('update');
        $i->_p['from'] = $table;
        return $i;
    }

    /**
     * internal constructor
     *
     * @param string $op query operation
     */
    protected function __construct($op)
    {
        $this->reset();
        $this->_op = $op;
        $this->_dirty = true;
    }

    /**
     * reset inserted parameters
     *
     * @return $this
     */
    public function reset()
    {
        $this->_p = array(
            'values' => array(),
            'set'    => array(),
            'select' => array(),
            'leftjoin' => array(),
            'distinct' => false,
            'from'   => '',
            'where'  => array(),
            'wherecount'=>0,
            'having' => '',
            'group'  => '',
            'order'  => array(),
            'limit'  => '',
            'offset' => 0,
            'duplicatedkey' => array()
        );
        $this->_dirty = true;
        return $this;
    }

    /**
     * distinct flag
     *
     * @param bool $bool
     */
    public function distinct($bool=true)
    {
        $this->_p['distinct'] = $bool;
        $this->_dirty = true;
        return $this;
    }

    /**
     * INSERT|UPDATE: values
     *
     * @param mixed $mixed
     */
    public function values($mixed) {
        if (empty($mixed) || $mixed == false)
        {
            return $this;
        }
        $this->_p['values'] = $mixed;
        $this->_dirty = true;
        return $this;
    }

    /**
     * INSERT: on duplicate update keys
     *
     * @param mixed $mixed key| array of keys to update
     */
    public function duplicatekey($mixed) {
        if (empty($mixed)) {
            return $this;
        }

        if (is_array($mixed)) {
            $this->_p['duplicatedkey'] = array_merge($this->_p['duplicatedkey'], $mixed);
        } else {
            $this->_p['duplicatedkey'][] = $mixed;
        }
        $this->_dirty = true;
        return $this;
    }

    /**
     * SELECT: from
     *
     * @param string|array $mixed
     */
    public function from($mixed)
    {
        if (empty($mixed) || $mixed == false)
        {
            return $this;
        }

        if (is_array($mixed)){
            $this->_p['from'] = $mixed;
        } else {
            $this->_p['from'] = array($mixed);
        }
        $this->_dirty = true;
        return $this;
    }

    /**
     * SELECT: left join
     * Enter description here ...
     * @param $table
     * @param $on
     */
    public function leftJoin($table, $on)
    {
        $this->_p['leftjoin'][] = array($table, $on);
        $this->_dirty = true;
        return $this;
    }

    /**
     * INSERT|UPDATE|SELECT|DELETE: where clause
     *
     * @param array $mixed where key or array of wheres
     * @param mixed $value key value
     * @param bool  $if    to identify resursive where
     */
    public function where($mixed, $value='__NOTHING__', $if=true)
    {
        if (!$if || empty($mixed) || $mixed == false) {
            return $this;
        }

        //print_r($mixed);

        if (is_array($mixed) && is_string($value) && strcmp($value,"__NOTHING__")==0){
            foreach($mixed as $key => $val){
                if (is_numeric($key) && is_array($val)) {
                    //recruveively add where
                    $this->where($val);
                } else {
                    //add '=?' if '?' does not exist
                    if (strpos($key,'?') == false) {
                        $key = $key.'=?';
                    }
                    $this->_p['where'][$this->_p['wherecount']][$key] = $val;
                }
            }
        } elseif (!is_array($mixed)) {
           if (is_string($value) && strcmp($value,"__NOTHING__")==0) {
               if (strpos($mixed,'?') == false) {
                   $this->_p['where'][$this->_p['wherecount']][$mixed] = '__NOTHING__';
               } else {
                   throw new ModelException("no value is specified for where statement $mixed", ERROR_MYSQL_PARAM);
               }
           } else {
               if (strpos($mixed,'?') == false){
                   $this->_p['where'][$this->_p['wherecount']][$mixed.'=?'] = $value;
               } else {
                   $this->_p['where'][$this->_p['wherecount']][$mixed] = $value;
               }
           }
        }
        $this->_dirty = true;
        return $this;
    }

    /**
     * to enter to new set of OR relationship in where clause
     *
     * @param array $mixed where key or array of wheres
     * @param mixed $value key value
     * @param bool  $if    to identify resursive where
     */
    public function orWhere($mixed, $value='__NOTHING__', $if=true)
    {
        $this->_p['wherecount']++;
        return $this->where($mixed, $value, $if);
    }

    /**
     * INSERT|UPDATE: set
     *
     * @param mixed $mixed key or set array
     * @param mixed $value key value
     */
    public function set($mixed, $value='')
    {
        if (is_array($mixed)){
            $this->_p['set'] = array_merge($this->_p['set'], $mixed);
        } else {
           $this->_p['set'][$mixed] = $value;
        }
        $this->_dirty = true;
        return $this;
    }

    /**
     * SELECT: having
     *
     * @param mixed $mixed
     */
    public function having($mixed)
    {
        if (empty($mixed) || $mixed == false) {
            return $this;
        }
        $this->_p['having'] = $mixed;
        $this->_dirty = true;
        return $this;
    }

    /**
     * SELECT: group
     *
     * @param mixed $mixed
     */
    public function group($mixed)
    {
        if (empty($mixed) || $mixed == false) {
            return $this;
        }

        if (is_array($mixed)){
            $this->_p['group'] = $mixed;
        } else {
            $this->_p['group'][] = $mixed;
        }

        $this->_p['select'][] = 'COUNT(*) as __count';
        $this->_dirty = true;
        return $this;
    }

    /**
     * SELECT: order by
     *
     * @param mixed $mixed key or order array
     * @param string $order asc/desc
     */
    public function order($mixed, $order='DESC')
    {
        if (empty($mixed) || $mixed == false) {
            return $this;
        }

        if (is_array($mixed)){
            $this->_p['order'][] = $mixed;
        } else {
            $this->_p['order'][] = array($mixed, strtoupper($order) == 'DESC'? 'DESC':'ASC');
        }
        $this->_dirty = true;
        return $this;
    }

    /**
     * SELECT: limit
     *
     * @param int $int
     */
    public function limit($int)
    {
        if (empty($int) || $int == false) {
            return $this;
        }
        $this->_p['limit'] = $int;
        $this->_dirty = true;
        return $this;
    }

    /**
     *
     *
     * @param int $int
     */

    /**
     * SELECT: offset
     * will replace with where for best practice if sufficient info is obtained
     *
     * @param mixed  $mixed int or key or field:value
     * @param mixed  $value value to offset
     * @param string $order asc/desc
     */
    public function offset($mixed, $value='', $order='DESC')
    {
        if (empty($mixed) || $mixed == false) {
            return $this;
        }

        if (is_array($mixed)){
            list($field,$value) = $mixed;
        } else {
            $field = $mixed;
        }

        if (!is_int($field) && !empty($value)){
            //add a where to replace offset
            $key = strtoupper($order) == 'DESC'? "$field<?": "$field>?";

            //add where at each OR group
            if (!isset($this->_p['where'][0])) {
                $this->_p['where'][0] = array();
            }
            foreach($this->_p['where'] as &$where) {
                $where[$key] = $value;
            }

            //add this as highest ordering criteria
            array_unshift($this->_p['order'], array($field, $order));
        } else {
            //numeric handling
            $this->_p['offset'] = $field;
        }
        $this->_dirty = true;
        return $this;
    }

    /**
     * main function call to get sql
     *
     * @param array $spec database specification
     *
     * @return string
     */
    public function getSql($spec = array())
    {
        if ($this->_dirty) {
            $this->_params = array();
            $this->_query = $this->{'gen'.ucfirst($this->_op).'Stmt'}();
            $this->_dirty = false;
        }
        return $this->_query;
    }

    /**
     * return array of parameters
     *
     * @return array
     */
    public function getParams()
    {
        if ($this->_dirty) {
            $this->_params = array();
            $this->{'gen'.ucfirst($this->_op).'Stmt'}();
            $this->_dirty = false;
        }
        return $this->_params;
    }

    public function getStmtIdx($params){
        $idx = '';
        $rtnarr = array();

        foreach($params as $param){
            if (is_array($param)){
                $subrtnarry = $this->getStmtIdx($param);
                $idx .= array_shift($subrtnarry);
                $rtnarr = array_merge($rtnarr, $subrtnarry);
            } elseif (is_float($param)){
                $idx .= 'd';
                $rtnarr[] = $param;
            } elseif (is_int($param) || is_bool($param)) {
                $idx .= 'i';
                $rtnarr[] = $param;
            } else {
                $idx .= 's';
                $rtnarr[] = $param;
            }
        }

        array_unshift($rtnarr, $idx);
        return $rtnarr;
    }

    public function getOperation() {
        return $this->_op;
    }

    private function _getWhere($where){
        if (empty($where)) {
            return '';
        }

        $wherestr = array();
        foreach($where as $a){
            if (is_null($a) || !is_array($a)){
                continue;
            }
            //$where = $this->_addDot(array_keys($a));
            $where = array_keys($a);
            $wherestr[] = '( ('.implode(') && (', $where).') )';

            //parameters
            foreach(array_values($a) as $v){
                if ($v == "__NOTHING__") {
                    continue;
                }
                $this->_params = is_array($v)
                    ? array_merge($this->_params, $v)
                    : array_merge($this->_params, array($v));
            }
        }
        return ' WHERE '.implode(' || ', $wherestr);
    }

    protected function genSelectStmt()
    {
        $sql = '';

        $select = implode(', ',$this->_p['select']);

        $distinct =  $this->_p['distinct']? 'DISTINCT ': '';

        $from = implode(', ',$this->_p['from']);

        $sql = "SELECT {$distinct}{$select} FROM {$from}";

        if (!empty($this->_p['leftjoin'])){
            $leftjoin = '';
            foreach($this->_p['leftjoin'] as $joins) {
                $leftjoin .= " LEFT JOIN {$joins[0]} ON {$joins[1]}";
            }
            $sql .= $leftjoin;
        }

        $sql .= $this->_getWhere($this->_p['where']);

        if (!empty($this->_p['group'])){
            $sql .= ' GROUP BY '.implode(' , ', $this->_p['group']);
        }
        if (!empty($this->_p['having'])){
            $sql .= ' HAVING '.$this->_p['having'];
        }
        if (!empty($this->_p['order'])){
            $order = array();
            foreach($this->_p['order'] as $o) {
                //if 'DESC|ASC' is plugged'
                $order[] = strpos($o[0], ' ')? $o[0]: $o[0].' '.$o[1];
            }
            //$order = $this->_addDot($order);
            $sql .= ' ORDER BY '.implode(',', $order);
        }

        if (!empty($this->_p['limit']) || !empty($this->_p['offset'])){

            //cases when limit is not specified
            if (empty($this->_p['limit']) || $this->_p['limit'] < 0){
                $this->_p['limit'] = 0;
            }
            //offset is inside limit
            $offset = !empty($this->_p['offset'])? $this->_p['offset'].',': '';
            $sql .= ' LIMIT '.$offset.$this->_p['limit'];
        }

        return $sql;
    }

    protected function genInsertStmt()
    {
        $sql = "INSERT INTO {$this->_p['from']} (%s) VALUES(%s)";

        $keyarr = array_keys($this->_p['values']);
        $valuearr =  array_fill(0, count($this->_p['values']), '?');

        //add into params list
        $this->_params = array_merge($this->_params, array_values($this->_p['values']));

        $keys = '`'.implode('`, `', $keyarr).'`';
        $vals = implode(', ',$valuearr);

        //on duplicated update
        if (!empty($this->_p['duplicatedkey'])){
            $dups = array();
            foreach($this->_p['values'] as $k=>$v){
                if (in_array($k, $this->_p['duplicatedkey'])) {
                    $dups[] = "`$k`=?";
                    $this->_params[] = $v;
                }
            }
            if (!empty($dups)) {
                $sql .= ' ON DUPLICATE KEY UPDATE '. implode(' , ', $dups);
            }
        }

        $sql = sprintf($sql, $keys, $vals);
        return $sql;
    }

    protected function genDeleteStmt()
    {
        $sql = "DELETE FROM {$this->_p['from']}";

        $sql .= $this->_getWhere($this->_p['where']);

        if (!empty($this->_p['order'])){
            $order = array();
            foreach($this->_p['order'] as $o) {
                //if 'DESC|ASC' is plugged'
                $order[] = strpos($o[0], ' ')? $o[0]: $o[0].' '.$o[1];
            }
            //$order = $this->_addDot($order);
            $sql .= ' ORDER BY '.implode(',', $order);
        }

        if (!empty($this->_p['limit'])){
            //offset is inside limit
            $sql .= ' LIMIT '.$this->_p['limit'];
        }
        return $sql;
    }

    protected function genUpdateStmt()
    {
        $sql = "UPDATE {$this->_p['from']} SET ";

        $set = array();
        foreach($this->_p['set'] as $k=>$v){
            $set[] = "`$k`=?";
            $this->_params[] = $v;
        }

        $sql .= implode(' , ', $set);

        $sql .= $this->_getWhere($this->_p['where']);

        if (!empty($this->_p['order'])){
            $order = array();
            foreach($this->_p['order'] as $o) {
                //if 'DESC|ASC' is plugged'
                $order[] = strpos($o[0], ' ')? $o[0]: $o[0].' '.$o[1];
            }
            //$order = $this->_addDot($order);
            $sql .= ' ORDER BY '.implode(',', $order);
        }

        if (!empty($this->_p['limit'])){
            //offset is inside limit
            $sql .= ' LIMIT '.$this->_p['limit'];
        }

        return $sql;
    }
}