<?php


namespace Intahwebz\DB;


interface StatementWrapperFactory {

    /**
     * @return StatementWrapper
     */
    function create($statement, $calledFromString);
} 