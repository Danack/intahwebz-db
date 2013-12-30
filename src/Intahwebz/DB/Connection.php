<?php

namespace Intahwebz\DB;

interface Connection {
    
    function activateTransaction();

    function commit();
    
    function getLastError();

    function close($closeCached = FALSE);

    function rollback();

    function selectSchema($schema);
    
    /**
     * @param $queryString
     * @param bool $log
     * @param int $callstackLevel
     * @return MySQLiStatement
     */
    function prepareStatement($queryString, $log = FALSE, $callstackLevel = 0);

    /**
     * @param $queryString
     * @param bool $log
     * @return MySQLiStatement
     */
    function prepareAndExecute($queryString, $log = FALSE);

    /**
     * @param $queryString
     * @return \mysqli_result
     */
    function directExecute($queryString);
}


