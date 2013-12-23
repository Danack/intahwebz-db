<?php


namespace Intahwebz\DB;


class MockDBConnection implements DBConnection {
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
     * @return StatementWrapper
     */
    function prepareStatement($queryString, $log = false, $callstackLevel = 0) {


        //return new MockStatementWrapper();
    }

    /**
     * @param $queryString
     * @param bool $log
     * @return StatementWrapper
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

