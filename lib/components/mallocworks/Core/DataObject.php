<?php
/**
 * base object for data manipulations
 * 2011-02-20: remove caching functionality to make it more lightweighted and less error prone
 * @author ycheung
 *
 */
namespace Utopia\Components\Core;

class DataObject implements \Iterator
    //, \ArrayAccess (array access not implemented yet)
{
    private $_data;               //actual data
    private $_symlinks;           //symlinks

    private $_curpointer;         //iterator: current array
    private $_curseq;             //iterator: current array sequence

    /* =================
     * magic functions
     * =================
     */

    /**
     * constructor
     *
     * @param string $name dataobject name for caching
     * @param array $data initial data
     */
    public function __construct(array $data=array()) {
        if (!defined('SEP')) { define('SEP', '>'); };
        if (!defined('DS'))  { define('DS', '/'); };

        $this->_data = $data;
        $this->_symlinks = array();
    }

    public function __destruct(){
    }

    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __isset($name) {
        return $this->exists($name);
    }

    public function __toString() {
        return var_export($this->_data, true);
    }

    /* =================
     * data access operations
     * =================
     */

    /**
     * set variables
     * if value is null, $name is the full array to insert
     *
     * @param string $name      params name "A>B>C"
     * @param string $value     default value
     * @param bool   $overwrite if overwrite if exist
     *
     * @return AbstractData
     */
    public function set($name, $value, $overwrite=true){

        if (empty($name)) {
            $this->_data = $value;
            return $this;
        }

        //replace symlinks
        if (!empty($this->_symlinks) && is_array($this->_symlinks)){
            $name = str_replace(array_keys($this->_symlinks), array_values($this->_symlinks), $name);
        }

        //locating
        $name = $this->_replaceSymlink($name);
        $levels = explode(SEP, $name);
        $current = &$this->_data;
        for($i=0; $i<count($levels)-1; $i++){
            $lv = $levels[$i];
            if (!is_array($current) && !$overwrite){
                //errorous case - mid level is not array
                return $this;
            } elseif (!is_array($current)){
                //create current level as array
                $current = array();
            }

            if(!isset($current[$lv])) {
                //create a new array case
                $current[$lv] = array();
            }
            //normal case
            $current = &$current[$lv];
        }

        //inserting
        $last = $levels[count($levels)-1];
        if (!is_array($current)) {
            //not an array
            if ($overwrite){
                $current = array();
                $current[$last] = $value;
            }
        } elseif (isset($current[$last]) && !$overwrite){
            //an array, but cannot be written
        } else {
            //normal case, write
            $current[$last] = $value;
        }
        return $this;
    }

    /**
     * get variables
     *
     * @param string $name     params name "A>B>C"
     * @param string $default  default value
     *
     * @return mixed
     */
    public function get($name='', $default=null){
        //empty name case
        if (empty($name)) {
            return $this->_data;
        }

        //path location
        $name = $this->_replaceSymlink($name);
        $levels = explode(SEP, $name);
        $current = &$this->_data;
        for($i=0; $i<count($levels); $i++){
            $lv = $levels[$i];
            if (is_array($current) && isset($current[$lv])){
                $current = &$current[$lv];
            } else {
                return $default;
            }
        }

        return $current;
    }

    /**
     * get keys of an array
     *
     * @param string $name     params name "A>B>C"
     *
     * @return array
     */
    public function keys($name){
        $val = $this->get($name, false);
        return $val === false? array(): array_keys($val);
    }

    /**
     * get counts of an array
     *
     * @param string $name params name "A>B>C"
     *
     * @return integer
     */
    public function count($name){
        $val = $this->get($name, false);
        return is_array($val)? count($val): false;
    }

    /**
     * add symlink
     *
     * @param string $name     params name "A>B>C"
     * @param string $replace  name to replace
     *
     * @return this
     */
    public function ln($name, $replace) {
        $this->_symlinks[$replace] = $name;
        return $this;
    }

    /**
     * remove symlink
     *
     * @param string $symlink name of symlink
     *
     * @return this
     */
    public function delln($symlink) {
        if (isset($this->_symlinks[$symlink])) {
            unset($this->_symlinks[$symlink]);
        }
        return $this;
    }

    /**
     * nice outputing
     *
     * @param string $name        params name "A>B>C"
     * @param bool   $nottoscreen if string is return
     *
     * @return string
     */
    public function pr($name='', $nottoscreen=false){
        if (empty($name)) {
            print_r($this->_data, $nottoscreen);
        } else {
            return print_r($this->get($name), $nottoscreen);
        }
    }

	/**
     * unset variables
     *
     * @param string $name params name "A>B>C"
     *
     * @return this
     */
    public function del($name){
        $name = $this->_replaceSymlink($name);
        $levels = explode(SEP, $name);
        $current = &$this->_data;
        //get to the second-to-last level
        for($i=0; $i<count($levels)-1; $i++){
            $lv = $levels[$i];
            if (is_array($current) && isset($current[$lv])){
                $current = &$current[$lv];
            } else {
                return $this;
            }
        }
        //deleting
        $last = $levels[count($levels)-1];
        unset($current[$last]);
        return $this;
    }

    /**
     * insert a data in the middle of an array
     *
     * @param string $name     params name "A>B>C"
     * @param array  $value    data to insert
     * @param array  $position where the data be inserted
     *
     * @return this
     */
    public function insert($name, $value, $position){
        $var = $this->get($name, array());
        if (!empty($var) && !is_array($var)){
            return $this;
        } elseif(empty($var)) {
            $var = array();
        }

        array_splice($var, $position, 0, array($value));
        $this->set($name, $var);
        return $this;
    }

    /**
     * merge an array to destination
     *
     * @param string $name     params name "A>B>C"
     * @param array  $value    data to insert
     * @param array  $position where the data be inserted
     *
     * @return this
     */
    public function merge($name, $array){
        if (!is_array($array)){
            return $this;
        }

        $var = $this->get($name, array());
        if (!empty($var) && !is_array($var)){
            return $this;
        } elseif(empty($var)) {
            $var = array();
        }

        $var = array_merge($var, $array);
        $this->set($name, $var);
        return $this;
    }

    /**
     * check if a key exists
     *
     * @param string $name key to check
     *
     * @return bool
     */
    public function exists($name) {
        return !is_null($this->get($name, null));
    }

    /**
     * check if a key is empty
     *
     * @param string $name key to check
     *
     * @return bool
     */
    public function isempty($name) {
        $val = $this->get($name, '');
        return empty($val);
    }

    /**
     * check if a value exists in a key array
     *
     * @param string $name key to check
     * @param string $value to search
     *
     * @return bool
     */
    public function in($name, $value) {
        $val = $this->get($name, '');
        return in_array($value, $val);
    }

    /**
     * push a value into a key holder
     *
     * @param string $name  key to push
     * @param mixed  $value value to push
     *
     * @return this
     */
    public function push($name, $value) {
        //add one item at the end of {$name}, error if $name exists and not array
        $var = $this->get($name);
        if (is_array($var)) {
            array_push($var, $value);
        } elseif (is_null($var)) {
            $var = array($value);
        } else {
            return $this;
        }

        //put the new array back
        $this->set($name, $var);
        return $this;
    }

    /**
     * pop a value from a key holder
     *
     * @param string $name  key to push
     *
     * @return mixed
     */
    public function pop($name) {
        //remove and return last item at the end of {$name},
        //error if $name is not array, return null
        $var = $this->get($name);
        if (is_array($var)) {
            $rtn = array_pop($var);
            //put the new array back
            $this->set($name, $var);
            return $rtn;
        } else {
            return null;
        }
    }

    /**
     * unshift a value into a key holder
     *
     * @param string $name  key to push
     * @param mixed  $value value to push
     *
     * @return this
     */
    public function unshift($name, $value) {
        //add one item in the front of {$name}, error if $name exists and not array
        //array_unshift(self::$_searchdir, $searchdirs[$i]);
        $var = $this->get($name);
        if (is_array($var)) {
            array_unshift($var, $value);
        } elseif (is_null($var)) {
            $var = array($value);
        } else {
            return $this;
        }

        //put the new array back
        $this->set($name, $var);
        return $this;
    }

    /**
     * shift a value into a key holder
     *
     * @param string $name  key to push
     *
     * @return mixed
     */
    public function shift($name) {
        //remove and return one item in front of {$name}
        //error if $name is not array, return null
        $var = $this->get($name);
        if (is_array($var)) {
            $rtn = array_shift($var);
            //put the new array back
            $this->set($name, $var);
            return $rtn;
        } else {
            return null;
        }
    }

    /**
     * get a selection range of a key
     *
     * @param string $name key to push
     * @param int    $from from index
     * @param int    $to   to index
     *
     * @return array
     */
    public function getRangeValues($name, $from, $to) {
        //getting the values from range, no matter what it is
        $var = $this->get($name);
        if (!is_array($var)) {
            return array();
        }
        return is_array($var)? array_slice(array_values($var), $from, $to): array();
    }

    /**
     * get the keys of a selection range
     *
     * @param string $name key to push
     * @param int    $from from index
     * @param int    $to   to index
     *
     * @return array
     */
    public function getRangeKeys($name, $from, $to) {
        //getting the keys ofrom range, no matter what it is
        //getting the values from range, no matter what it is
        $var = $this->get($name);
        return is_array($var)? array_slice(array_keys($var), $from, $to): array();
    }

    /**
     * remove item of a range of the key
     *
     * @param string $name key to push
     * @param int    $from from index
     * @param int    $to   to index
     *
     * @return this
     */
    public function delRange($name, $from, $to=-1) {
        //removing the items from range
        $var = $this->get($name);
        if (!is_array($var)) {
            return $this;
        }

        //to = -1 is to delete rest of array
        if ($to == -1){
            $to = count($var);
        }
        array_splice($var, $from, $to);
        $this->set($name, $var);
        return $this;
    }

    /*
     * =================
     * iterator methods
     * =================
     */

    /**
     * set the current key to focus on
     *
     * @param this
     */
    public function setPointer($name) {
        $this->_curpointer = $name;
        return $this;
    }

    /**
     * remove the current key to focus on
     *
     * @param this
     */
    public function delPointer($name) {
        //remove keys from the pointer
        $this->del($this->_curpointer.SEP.$name);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current() {
        //return current item
        $values = $this->getRangeValues($this->_curpointer, $this->_curseq, 1);
        return isset($values[0])? $values[0]: null;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key() {
        //return current key
        $keys = $this->getRangeKeys($this->_curpointer, $this->_curseq, 1);
        return isset($keys[0])? $keys[0]: null;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next() {
        //jump to next pointer
        $this->_curseq++;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind() {
        //jump to previous pointer
        $this->_curseq = 0;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid() {
        //check if current item exists
        $values = $this->getRangeKeys($this->_curpointer, $this->_curseq, 1);
        return isset($values[0]);
    }

    /*
     * =================
     * ArrayAccess implementation
     * =================
     */
//    public function offsetExists ($offset) {}
//    public function offsetGet ($offset) {}
//    public function offsetSet ($offset, $value) {}
//    public function offsetUnset ($offset) {}

    /*
     * =================
     * private methods
     * =================
     */
    private function _replaceSymlink($name) {
        return !empty($this->_symlinks) && is_array($this->_symlinks)?
            str_replace(array_keys($this->_symlinks), array_values($this->_symlinks), $name): $name;
    }
}
