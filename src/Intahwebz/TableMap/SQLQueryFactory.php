<?php


namespace Intahwebz\TableMap;

use Intahwebz\DB\DBConnection;

class SQLQueryFactory {

    /**
     * @var \Intahwebz\DB\DBConnection
     */
    private $dbConnection;

    function __construct(DBConnection $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    function create() {
        return new SQLQuery($this->dbConnection);
    }

    function insertIntoMappedTable(TableMap $tableMap, $data) {
        $sqlQuery = new SQLQuery($this->dbConnection);
        return $sqlQuery->insertIntoMappedTable($tableMap, $data);
    }
}

 