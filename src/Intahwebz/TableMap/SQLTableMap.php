<?php


namespace Intahwebz\TableMap;


abstract class SQLTableMap extends TableMap {

    function __construct() {
        $this->initTableDefinition($this->getTableDefinition());
    }

    abstract function getTableDefinition();

    /**
     * @return Relation|null
     */
    function getSelfClosureRelation() {
        foreach ($this->relations as $relation) {
            if ($relation->getType() == Relation::SELF_CLOSURE) {
                return $relation;
            }
        }
        
        return null;
    }
}

