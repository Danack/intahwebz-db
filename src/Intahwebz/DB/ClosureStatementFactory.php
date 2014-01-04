<?php


namespace Intahwebz\DB;


class ClosureStatementFactory implements StatementFactory {

    private $closure;
    
    function __construct(callable $closure) {
        $this->closure = $closure;
    }
    
    function create($statement, $calledFromString) {
        $function = $this->closure;
        $statementWrapper = $function($statement, $calledFromString);
        
        return $statementWrapper;
    }
}
