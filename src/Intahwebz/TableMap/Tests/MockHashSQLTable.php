<?php


namespace Intahwebz\TableMap\Tests;

use Intahwebz\TableMap\SQLTableMap;

class MockHashSQLTable extends SQLTableMap  {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockHash',
            'columns' => array(
                array('username',),
                array('password', 'type' => 'hash'),
            )
        );

        return $tableDefinition;
    }

//    function getClassName() {
//        return "MockHash";
//    }
}

 