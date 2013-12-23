<?php


namespace Intahwebz\TableMap\Tests;

use Intahwebz\TableMap\SQLTableMap;

class MockRandDataSQLTable extends SQLTableMap  {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockRandData',
            'columns' => array(
                array('mockRandDataID', 'primary' => true, 'autoInc' => true ),
                array('title'),
                array('text', 'type' => 'MEDIUMTEXT' ),
            )
        );

        return $tableDefinition;
    }

    function getClassName() {
        return "MockNote";
    }
}

 