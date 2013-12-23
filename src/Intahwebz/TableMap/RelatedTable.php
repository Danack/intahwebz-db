<?php

namespace Intahwebz\TableMap;

class RelatedTable {

    private $tableMap;

    private $relationType;

    function __construct(TableMap $tableMap, $relationType) {
        $this->tableMap = $tableMap;
        $this->relationType = $relationType;
    }

    function getRelationshipTable(TableMap $firstTable) {
        $className = $firstTable->getTableName().$this->tableMap->getTableName()."Relation";
        
        $namespace = getNamespace($firstTable);
        $namespaceClassName = $namespace."\\".$className;

        return new $namespaceClassName();
    }

    function getTableMap() {
        return $this->tableMap;
    }
}