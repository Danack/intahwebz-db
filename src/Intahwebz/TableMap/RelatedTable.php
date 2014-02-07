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

        $className = sprintf(
            '%sX%sX%sRelation', 
            $firstTable->getTableName(),
            $this->tableMap->getTableName(),
            $this->relationName
        );

        $namespace = getNamespace($firstTable);
        $namespaceClassName = $namespace."\\".$className;

        return new $namespaceClassName();
    }

    function getTableMap() {
        return $this->tableMap;
    }
}