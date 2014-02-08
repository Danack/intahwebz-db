<?php


namespace Intahwebz\TableMap\Tests\Table;

use Intahwebz\TableMap\SQLTableMap;


class TagTable extends SQLTableMap  {

    function getTableDefinition() {

         $tableDefinition = array(
             'schema' => 'basereality',
             'tableName' => 'tag',
             'columns' => array(
                 array('tagID', 'primary' => true, 'autoInc' => true ),
                 array('contentID', 'type' => 'i'),
                 array('text'),
             ),
             'indexColumns' => array(
                 array('contentID',),
             ),
        );

        return $tableDefinition;
    }
}

 