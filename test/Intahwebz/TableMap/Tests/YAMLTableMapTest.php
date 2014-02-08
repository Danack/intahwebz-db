<?php

use Intahwebz\YamlPath;
use Intahwebz\TableMap\YAMLQuery;
use Intahwebz\TableMap\YAMLQueryFactory;

class YAMLTableMapTest extends \PHPUnit_Framework_TestCase {


    /**
     * @var \Intahwebz\TableMap\Tests\Table\MockYAMLTable;
     */
    private $testTable = null;


    private $testData = [
        'name' => 'Danack',
        'value' => 12,
        'type' => 'SomeType',
    ];


    static function setUpBeforeClass() {
        $filename = realpath(__DIR__.'/MockYaml.yml');
        @unlink($filename);
    }

    function setUp() {
        $datapath = new YamlPath(realpath(__DIR__));
        $this->testTable = new \Intahwebz\TableMap\Tests\Table\MockYAMLTable($datapath);
    }


    function testCreateFile() {
        $yamlData = $this->testTable->createYAMLData();
        $this->testTable->writeYAML($yamlData);
    }


    function testInsert() {
        $this->testTable->insertIntoMappedTable($this->testData);
    }

    function testWherePrimary() {
        $queryFactory = new YAMLQueryFactory();
        $query = $queryFactory->create();
        $query->table($this->testTable)->wherePrimary(0);
        $data = $query->fetch();
        $data = $data[0];

        foreach ($this->testData as $key => $value) {
            $this->assertArrayHasKey('MockYaml.'.$key, $data, 'Value is missing');
            $this->assertEquals($data['MockYaml.'.$key], $value, 'Value is wrong.');
        }
    }


    function testWhereColumn() {
        $query = new YAMLQuery();
        $query->table($this->testTable)->whereColumn('name', 'Danack');
        $contentArray = $query->fetch();
        $data = $contentArray[0];

        foreach ($this->testData as $key => $value) {
            $this->assertArrayHasKey('MockYaml.'.$key, $data, 'Value is missing');
            $this->assertEquals($data['MockYaml.'.$key], $value, 'Value is wrong.');
        }
    }


    function testTwoTables() {
        $this->setExpectedException('\BadFunctionCallException');
        $query = new YAMLQuery();
        $query->table($this->testTable)->whereColumn('name', 'Danack');
        $query->table($this->testTable)->whereColumn('value', 12);

        $query->fetch();
    }

    function testMissingTable() {
        $this->setExpectedException('\BadFunctionCallException');
        $query = new YAMLQuery();

        $query->fetch();
    }


    function	testDelete(){
        $this->setExpectedException('\BadFunctionCallException');
        $query = new YAMLQuery();
        $query->delete();
    }

    function testCount() {
        $this->setExpectedException('\BadFunctionCallException');
        $query = new YAMLQuery();
        $query->count();
    }

    function testUpdate() {
        $query = new YAMLQuery();
        $query->table($this->testTable)->whereColumn('name', 'Danack');

        $query->setValue('value', 17);
        $query->update();

        $query = new YAMLQuery();
        $query->table($this->testTable)->whereColumn('value', 17);
        $data = $query->fetch();

        $this->assertEquals(1, count($data), "Updated value not found.");
    }
}
 