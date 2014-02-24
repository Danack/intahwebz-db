<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class MockCommentTable extends SQLTableMap  {

    function getTableDefinition() {
         $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockComment',
            'columns' => array(
                ['mockCommentID', 'primary' => true, 'autoInc' => true ],
                ['text'],
            ),
             'relations' => [
                 '\Intahwebz\TableMap\Tests\Table\commentRelation'
             ]
        );
        return $tableDefinition;
    }

    function isTreeLike() {
        return true;
    }
}

 