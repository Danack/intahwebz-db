<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;
use Intahwebz\Timer;

class TimedStatementWrapperFactory implements StatementWrapperFactory {

    private $logger;
    
    function __construct(LoggerInterface $logger, Timer $timer) {
        $this->logger = $logger;
        $this->timer = $timer;
    }
    
    function create($statement, $calledFromString) {
        $statementWrapper = new TimerProxyXStatementWrapper(
                                $statement, 
                                $calledFromString, 
                                $this->logger,
                                $this->timer
                            );

        return $statementWrapper;
    }
}