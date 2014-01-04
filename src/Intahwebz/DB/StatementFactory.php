<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;


interface StatementFactory {

    /**
     * @return Statement
     */
    function create(\mysqli_stmt $statement, $queryString, LoggerInterface $logger);
} 