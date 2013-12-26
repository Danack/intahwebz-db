<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;


class StandardStatementWrapperFactory implements StatementWrapperFactory {

    private $logger;
    
    function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @return StatementWrapper
     */
    function create($statement, $calledFromString) {
        $statementWrapper = new StatementWrapper($statement, $calledFromString, $this->logger);

        return $statementWrapper;
    }
}