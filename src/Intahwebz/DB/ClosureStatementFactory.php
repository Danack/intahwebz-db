<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;


class ClosureStatementFactory implements StatementFactory {

    private $closure;
    
    function __construct(callable $closure) {
        $this->closure = $closure;
    }

    function create(\mysqli_stmt $statement, $queryString, LoggerInterface $logger) {
        $function = $this->closure;
        $statementWrapper = $function($statement, $queryString, $logger);
        
        return $statementWrapper;
    }
}
