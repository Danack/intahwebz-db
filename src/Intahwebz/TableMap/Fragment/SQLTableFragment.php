<?php

namespace Intahwebz\TableMap\Fragment;

use Intahwebz\TableMap\QueriedTable;

class SQLTableFragment extends SQLFragment {

    /**
     * @var QueriedTable
     */
    var $queriedTableMap;
    
    /**
     * @var QueriedTable
     */
    var $queriedJoinTableMap = null;
    
    private $fetchColumns = true;

    function __construct(QueriedTable $tableMap, QueriedTable $joinTableMap = null) {
        parent::__construct('table');
        $this->queriedTableMap = $tableMap;
        $this->queriedJoinTableMap = $joinTableMap;
    }

    /**
     * @return \Intahwebz\TableMap\QueriedTable
     */
    public function getQueriedJoinTableMap() {
        return $this->queriedJoinTableMap;
    }

    /**
     * @return \Intahwebz\TableMap\QueriedTable
     */
    public function getQueriedTableMap() {
        return $this->queriedTableMap;
    }
    
    function setFetchColumns($boolean) {
        $this->fetchColumns = boolval($boolean);
    }

    /**
     * @return boolean
     */
    public function getFetchColumns() {
        return $this->fetchColumns;
    }
    
    
}
