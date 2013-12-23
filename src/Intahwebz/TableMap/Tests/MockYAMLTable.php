<?php

namespace Intahwebz\TableMap\Tests;


class MockYAMLTable extends \Intahwebz\TableMap\YAMLTableMap  {

    public $objectName = 'MockYaml';

//    function getClassName() {
//        return "MockYaml";
//    }

    protected $tableDefinition = array(
        'tableName' => 'MockYaml',
            'storage' => 'YAML',
            'columns' => array(
                array('MockYamlID', 'primary' => true, 'autoInc' => true ),
                array('name'),
                array('value'),
                array('type'),
            ),
    );

    function getTableDefinition() {
        return $this->tableDefinition;
    }
}

 