<?php


namespace Intahwebz\DB;


class MockDBConnection implements Connection {
    function activateTransaction() {
    }

    function commit() {
    }

    function getLastError() {
    }

    function close($closeCached = false) {
    }

    function rollback() {
    }

    function selectSchema($schema) {
    }

    /**
     * @param $queryString
     * @param bool $log
     * @param int $callstackLevel
     * @return MySQLiStatement
     */
    function prepareStatement($queryString, $log = false, $callstackLevel = 0) {


        //return new MockStatementWrapper();
    }

    /**
     * @param $queryString
     * @param bool $log
     * @return MySQLiStatement
     */
    function prepareAndExecute($queryString, $log = false) {
    }

    /**
     * @param $queryString
     * @return \mysqli_result
     */
    function directExecute($queryString) {
    }
}

