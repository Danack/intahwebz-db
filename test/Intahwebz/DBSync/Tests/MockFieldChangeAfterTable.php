<?php


namespace Intahwebz\DBSync\Tests;

use Intahwebz\TableMap\SQLTableMap;

class MockFieldChangeAfterTable extends SQLTableMap  {

    function getTableDefinition() {
        $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockNote',
            'columns' => array(
                array('mockNoteID', 'primary' => true, 'autoInc' => true ),
                array('title', 'type' => 'MEDIUMTEXT'),
                array('toBeAdded')
            )
        );

        return $tableDefinition;
    }

//    function getClassName() {
//        return "MockNote";
//    }
}



