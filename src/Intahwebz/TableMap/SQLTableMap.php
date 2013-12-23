<?php


namespace Intahwebz\TableMap;


abstract class SQLTableMap extends TableMap {

    function __construct() {
        $this->initTableDefinition($this->getTableDefinition());
    }

    abstract function getTableDefinition();


}

