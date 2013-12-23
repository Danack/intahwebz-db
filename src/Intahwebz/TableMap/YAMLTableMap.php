<?php

namespace Intahwebz\TableMap;

use Intahwebz\YamlPath;

abstract class YAMLTableMap extends TableMap{

    private $dataPath;

    abstract function getTableDefinition();

    function __construct(YamlPath $dataPath) {

        $this->dataPath = $dataPath;

        if ($this->getTableDefinition() == null) {
            throw new \InvalidArgumentException("Derived table has not set the definition.");
        }

        $this->initTableDefinition($this->getTableDefinition());
    }



    function getFileName() {
        $tableName = $this->tableName;

        $tableName = str_replace(".", "_", $tableName);
        $tableName = str_replace("/", "_", $tableName);

        return $this->dataPath->getSafePath('/', $tableName.".yml");
    }


    function deleteFromMappedTableCount() {

    }

    function updateMappedTable($params) {
        unused($params);
        throw new \BadFunctionCallException("Fark");
    }

    function createYAMLData() {
        $yaml = array();

        $primaryColumn = $this->getPrimaryColumn();

        $yaml['metaData'] = array();

        if($primaryColumn !== FALSE){
            $yaml['metaData'][$primaryColumn] = 0;
        }

        $yaml['tableData'] = array();

        return $yaml;
    }

    function getYAML() {
        $filename = $this->getFileName();
        $yaml = @file_get_contents($filename);
        if($yaml === FALSE){
            //TODO - should init the table data properly and add a primary ID
            throw new \Exception("Could not open YAML file [$filename] for YAML mapping.");
        }

        $data = yaml_parse($yaml);
        return $data;
    }

    function writeYAML($yamlData) {
        $filename = $this->getFileName();
        $yaml = yaml_emit($yamlData , YAML_UTF8_ENCODING, YAML_CRLN_BREAK);
        $result = file_put_contents($filename, $yaml);
        if($result === false){
            throw new \Exception("Failed to write YAMLTableMap file [$filename] with realpath ".realpath($filename).", data loss is likely.");
        }
    }

    function insertIntoMappedTable($data) {
        $nextID = null;
        $filename = $this->getFileName();
        $yamlData = $this->getYAML($filename);
        $primaryColumn = $this->getPrimaryColumn();

        if($primaryColumn !== false){
            // TODO - ought to be using autoinc check, not just assuming that primary columns
            // get auto-incremented.
            $nextID = $yamlData['metaData'][$primaryColumn];
            $yamlData['metaData'][$primaryColumn]++;
            $data[$primaryColumn] = $nextID;
        }

        $yamlData['tableData'][] = $data;

        $this->writeYAML($yamlData);

        return $nextID;
    }

}


