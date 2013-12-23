<?php

namespace Intahwebz\TableMap;

class SQLNullFragment extends SQLFragment{

    /**
     * @var QueriedTable
     */
    var $tableMap;

    /**
     * @var QueriedTable
     */
    var $nullTableMap;
    var $columnValues = array();

    var $nullTableMapAlias;

    //TODO - shouldn't be passing in alias
    function __construct(QueriedTable $tableMap, QueriedTable $nullTableMap, $nullTableAlias, $columnValues) {
        $this->tableMap = $tableMap;
        $this->nullTableMapAlias = $nullTableAlias;
        $this->nullTableMap = $nullTableMap;
        $this->columnValues = $columnValues;
    }

}


