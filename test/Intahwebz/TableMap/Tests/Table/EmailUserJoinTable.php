<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class EmailUserJoinTable extends SQLTableMap  {

    function getTableDefinition() {
         $tableDefinition = array(
            'schema' => 'mocks',
            'tableName' => 'emailuser',
            'columns' => array(
                array('emailID', 'type' => 'i', 'foreignKey' => 'email'),
                array('userID', 'type' => 'i', 'foreignKey' => 'user'),
            ),
        );
        return $tableDefinition;
    }
}