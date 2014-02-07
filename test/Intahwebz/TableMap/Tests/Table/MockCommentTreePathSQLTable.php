<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class MockCommentTreePathSQLTable extends SQLTableMap  {

    function getTableDefinition() {
         $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockComment_TreePaths',
            'columns' => array(
                ['mockCommentTreePathID', 'primary' => true, 'autoInc' => true ],
                ['ancestor', 'type' => 'i'],
                ['descendant', 'type' => 'i'],
                ['depth', 'type' => 'i'],
            ),
        );
        return $tableDefinition;
    }

//    function getClassName() {
//        //TODO - this should throw an exception, as this table dosesn't represent an
//        //instantiable class.
//        return parent::getClassName();
//    }
}

 