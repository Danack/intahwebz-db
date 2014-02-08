<?php


namespace Intahwebz\TableMap;


abstract class SQLTableMap extends TableMap {

    function __construct() {
        $this->initTableDefinition($this->getTableDefinition());
    }

    abstract function getTableDefinition();

    /**
     * @return null|SQLTableMap
     */
    function findRelationTable(TableMap $joinTableMap, $relationName = null) {
        
        echo "Looking for relation:";
        var_dump($joinTableMap);
        
        foreach ($this->relations as $relation) {
            if ($relation->matches($joinTableMap) == true) {
//                echo "using relation";
//                var_dump($relation);
                $inverseTable = $relation->getInverse();
                $joiningTable = new $inverseTable();
                echo "joining table";
                var_dump($joiningTable);
                return $joiningTable; 
                //return $relation;
            }
        }
        return null;
    }
}

