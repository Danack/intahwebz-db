<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;

use Intahwebz\TableMap\Relation;

class emailuserprimaryEmailRelation { //extends SQLTableMap  {

    function getRelationDefinition() {
        return array(
            'type' => Relation::ONE_TO_ONE_BIDIRECTIONAL,
            'owning' => '\Intahwebz\TableMap\Tests\Table\UserTable',
            'inverse' => '\Intahwebz\TableMap\Tests\Table\EmailTable',
            'tableName' => '\Intahwebz\TableMap\Tests\Table\EmailUserJoinTable'
        );
    }
    
    
    /*
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
    } */
}