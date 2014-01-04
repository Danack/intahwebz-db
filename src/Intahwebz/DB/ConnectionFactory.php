<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;



interface ConnectionFactory {
    function create(LoggerInterface $logger,
                    StatementFactory $statementWrapperFactory,
                    $host, $username, $password, $port, $socket);
}

 