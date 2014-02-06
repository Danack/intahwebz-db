<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;


class MySQLiStatementFactory implements StatementFactory {

    private $logger;
    
    function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param \mysqli_stmt $statement
     * @param $queryString
     * @param \Psr\Log\LoggerInterface $logger
     * @return MySQLiStatement
     */
    function create(\mysqli_stmt $statement, $queryString, LoggerInterface $logger) {
        $statementWrapper = new MySQLiStatement($statement, $queryString, $this->logger);

        return $statementWrapper;
    }
}