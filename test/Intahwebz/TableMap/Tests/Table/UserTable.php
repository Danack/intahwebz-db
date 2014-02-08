<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;



class UserTable extends SQLTableMap  {

    function getTableDefinition() {
         $tableDefinition = array(
             'schema' => 'mocks',
             'tableName' => 'user',
             'columns' => array(
                array('userID', 'primary' => true, 'autoInc' => true ),
                array('datestamp', 'type' => 'd'),
                ['firstName'],
                ['lastName'],
             ),
             'relations' => [
                 \Intahwebz\TableMap\Tests\Table\emailuserprimaryEmailRelation::class
             ]
        );
        return $tableDefinition;
    }
}

 