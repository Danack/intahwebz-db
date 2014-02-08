<?php

namespace Intahwebz\TableMap;



class QueriedSQLTable extends QueriedTable {

    /**
     * @var SQLTableMap
     */
    public $tableMap;

    /**
     * @var SQLQuery
     */
    public $abstractQuery;

    function __construct(\Intahwebz\TableMap\SQLTableMap $tableMap, $tableAlias, SQLQuery $abstractQuery) {
        $this->tableMap = $tableMap;
        $this->alias = $tableAlias;

        $this->abstractQuery = $abstractQuery;
    }

    function getTableMap() {
        return $this->tableMap;
    }

    function getQuery() {
        return $this->abstractQuery;
    }

    /**
     * @param QueriedSQLTable $queriedTableMap
     * @param null $relationName
     * @return SQLTableMap|null
     */
    function findRelationTable(QueriedTable $queriedTableMap, $relationName = null) {
        return $this->tableMap->findRelationTable($queriedTableMap->getTableMap(), $relationName);
    }
}

 