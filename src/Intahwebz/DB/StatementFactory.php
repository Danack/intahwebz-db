<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;


interface StatementFactory {

    /**
     * @param \mysqli_stmt $statement
     * @param $queryString
     * @param \Psr\Log\LoggerInterface $logger
     * @return Statement
     */
    function create(\mysqli_stmt $statement, $queryString, LoggerInterface $logger);
} 