<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;


class MySQLiStatementFactory implements StatementFactory {

    private $logger;
    
    function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @return MySQLiStatement
     */
    function create($statement, $queryString) {
        $statementWrapper = new MySQLiStatement($statement, $queryString, $this->logger);

        return $statementWrapper;
    }
}