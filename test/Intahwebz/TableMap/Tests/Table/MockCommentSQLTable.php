<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class MockCommentSQLTable extends SQLTableMap  {

    function getTableDefinition() {
         $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockComment',
            'columns' => array(
                ['mockCommentID', 'primary' => true, 'autoInc' => true ],
                ['text'],
                ['parent', 'type' => 'i' ]
            ),
        );
        return $tableDefinition;
    }

//    function getClassName() {
//        return "Content";
//    }

    function isTreeLike() {
        return true;
    }
}

 