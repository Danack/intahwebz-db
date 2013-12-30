<?php


namespace Intahwebz\DB;


interface StatementFactory {

    /**
     * @return Statement
     */
    function create($statement, $calledFromString);
} 