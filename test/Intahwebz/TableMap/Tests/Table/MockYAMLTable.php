<?php

namespace Intahwebz\TableMap\Tests\Table;


class MockYAMLTable extends \Intahwebz\TableMap\YAMLTableMap  {

    public $objectName = 'MockYaml';

    protected $tableDefinition = array(
        'schema' => 'yamltest',
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

 