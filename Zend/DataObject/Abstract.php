<?php

abstract class Zend_DataObject_Abstract implements ArrayAccess {

    /**
     *
     * @var string Table name
     */
    protected $_name;

    /**
     *
     * @var string
     */
    protected $_pk;

    /**
     *
     * @var Zend_Db_Table_Abstract
     */
    protected $_db;

    /**
     * 
     * @return Zend_Db_Table_Abstract
     */
    public function getDb() {
        if (!$this->_db instanceof Zend_Db_Adapter_Abstract) {
            $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
        }
        return $this->_db;
    }

    public function offsetExists($offset) {
        return (bool) $this->getDb()->fetchRow($this->_getSelect(array($this->_pk . ' = ?' => $offset)));
    }

    public function offsetGet($offset) {
        return $this->getDb()->fetchRow($this->_getSelect(array($this->_pk . ' = ?' => $offset)));
    }

    public function offsetSet($offset, $value) {
        return $this->getDb()->update($value, array($this->_pk . ' = ?' => $offset));
    }

    public function offsetUnset($offset) {
        return $this->getDb()->delete(array($this->_pk . ' = ?' => $offset));
    }

    protected function _getSelect($conds = array(), $opts = array()) {
        $sql = $this->getDb()->select()->from($this->_name);
        foreach ($conds as $cond => $bind) {
            $sql->where($cond, $bind);
        }
        foreach ($opts as $opt => $val) {
            $sql->$opt($val);
        }
        return $sql;
    }

    public function fetchAll($conds = array(), $opts = array()) {
        return $this->getDb()->fetchAll($this->_getSelect($conds, $opts));
    }

    public function describe() {
        return $this->getDb()->fetchAll('DESCRIBE ' . $this->_name);
    }

    public function update($id, $data) {
        try {
            $data = array_filter($data);
            $this->getDb()->update($this->_name, $data, array($this->_pk . ' = ?' => $id));
            return 'OK';
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function insert($data) {
        try {
            $data = array_filter($data);
            $this->getDb()->insert($this->_name, $data);
            return 'OK';
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getPk() {
        return $this->_pk;
    }

    public function find($id) {
        $data = $this->fetchAll(array($this->_pk . ' = ?' => $id));
        if ($data) {
            return $data[0];
        }
        return array();
    }

    public function delete($id) {
        return $this->getDb()->delete($this->_name, array($this->_pk . ' = ?' => $id));
    }

}
