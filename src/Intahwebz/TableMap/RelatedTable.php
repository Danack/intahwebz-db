<?php

namespace Intahwebz\TableMap;

class RelatedTable {

    private $tableMap;

    private $relationType;
    
    private $relationName;

    function __construct(TableMap $tableMap, $relationType, $relationName) {
        $this->tableMap = $tableMap;
        $this->relationType = $relationType;
        $this->relationName = $relationName;
    }

    function getRelationshipTable(TableMap $firstTable) {
        $className = $firstTable->getTableName().'X'.$this->tableMap->getTableName().'X'.$relationName."Relation";
        
        $namespace = getNamespace($firstTable);
        $namespaceClassName = $namespace."\\".$className;

        return new $namespaceClassName();
    }

    function getTableMap() {
        return $this->tableMap;
    }
}