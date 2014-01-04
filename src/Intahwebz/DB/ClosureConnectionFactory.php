<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;

class ClosureConnectionFactory {

    private $closure;

    function __construct(callable $closure) {
        $this->closure = $closure;
    }

    function create(LoggerInterface $logger,
                    StatementFactory $statementWrapperFactory,
                    $host, $username, $password, $port, $socket) {
        
        $function = $this->closure;
        $connection = $function($logger,
                                      $statementWrapperFactory,
                    $host, $username, $password, $port, $socket);

        return $connection;
    }
}
