<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class MockCommentTreePathTable extends SQLTableMap  {

    function getTableDefinition() {
         $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'mockComment_TreePaths',
            'columns' => array(
                ['mockCommentTreePathID', 'primary' => true, 'autoInc' => true ],
                ['ancestor', 'type' => 'i', 'foreignKey' => 'mockComment'],
                ['descendant', 'type' => 'i', 'foreignKey' => 'mockComment'],
                ['depth', 'type' => 'i'],
            ),
        );
        return $tableDefinition;
    }
}

 