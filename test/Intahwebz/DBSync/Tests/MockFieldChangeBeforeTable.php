<?php


namespace Intahwebz\DBSync\Tests;

use Intahwebz\TableMap\SQLTableMap;

class MockFieldChangeBeforeTable extends SQLTableMap  {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockNote',
            'columns' => array(
                array('mockNoteID', 'primary' => true, 'autoInc' => true ),
                array('title'),
                array('toBeDeleted')
            )
        );

        return $tableDefinition;
    }

//    function getClassName() {
//        return "MockNote";
//    }
}

 