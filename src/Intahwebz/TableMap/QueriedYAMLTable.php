<?php

namespace Intahwebz\TableMap;


class QueriedYAMLTable extends QueriedTable {

    /**
     * @var YAMLTableMap
     */
    public $tableMap;

    /**
     * @var YAMLQuery
     */
    public $abstractQuery;

    function __construct(\Intahwebz\TableMap\YAMLTableMap $tableMap, $tableAlias, YAMLQuery $abstractQuery) {
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

    function getYAML() {
        return $this->tableMap->getYAML();
    }

    function getObjectName() {
        return $this->tableMap->getObjectName();
    }

    function writeYAML($yamlData) {
        $this->tableMap->writeYAML($yamlData);
    }

    /**
     * @param \Intahwebz\TableMap\QueriedSQLTable|\Intahwebz\TableMap\QueriedTable $queriedTableMap
     * @param null $relationName
     * @throws \Exception
     * @return Relation
     */
    function findRelationTable(/** @noinspection PhpUnusedParameterInspection */
        QueriedTable $queriedTableMap, /** @noinspection PhpUnusedParameterInspection */
                               $relationName = null) {
        throw new \Exception("not implemented.");
    }
}
 