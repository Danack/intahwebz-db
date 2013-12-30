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
    function create($statement, $calledFromString) {
        $statementWrapper = new MySQLiStatement($statement, $calledFromString, $this->logger);

        return $statementWrapper;
    }
}