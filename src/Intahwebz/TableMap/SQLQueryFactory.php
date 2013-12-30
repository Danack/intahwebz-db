<?php


namespace Intahwebz\TableMap;

use Intahwebz\DB\Connection;

class SQLQueryFactory {

    /**
     * @var \Intahwebz\DB\Connection
     */
    private $dbConnection;

    function __construct(Connection $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    /**
     * @return SQLQuery
     */
    function create() {
        return new SQLQuery($this->dbConnection);
    }

    function insertIntoMappedTable(TableMap $tableMap, $data) {
        $sqlQuery = new SQLQuery($this->dbConnection);
        return $sqlQuery->insertIntoMappedTable($tableMap, $data);
    }
}

 