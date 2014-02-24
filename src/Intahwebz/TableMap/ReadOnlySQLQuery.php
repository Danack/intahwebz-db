<?php


namespace Intahwebz\TableMap;


class ReadOnlySQLQuery extends SQLQuery {

    function insertIntoMappedTable(TableMap $tableMap, $data, $foreignKeys = array()) {
        throw new \Exception("Not allowed");
    }
}

 