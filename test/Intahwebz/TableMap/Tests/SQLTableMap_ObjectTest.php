<?php

use Intahwebz\TableMap\SQLQueryFactory;
use Intahwebz\TableMap\TableMapWriter;

class SQLTableMap_ObjectTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Auryn\Provider
     */
    private $provider;

    /**
     * @var SQLQueryFactory
     */
    private $sqlQueryFactory;


    static function setUpBeforeClass() {

        $mocks = [
        ];

        $provider = createProvider($mocks);

        //This dumps all tables
        $dbSync = $provider->make('Intahwebz\DBSync\DBSync');
        $dbSync->processUpgradeForSchema('mocks', []);

        $tablesToUpgrade = [
            new Intahwebz\TableMap\Tests\Table\MockContentSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockNoteSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockHashSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockRandDataSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockCommentSQLTable(),
            new Intahwebz\TableMap\Tests\Table\MockCommentTreePathSQLTable(),
        ];

        /** @var $dbSync Intahwebz\DBSync\DBSync */
        $dbSync = $provider->make('Intahwebz\DBSync\DBSync');
        $dbSync->processUpgradeForSchema('mocks', $tablesToUpgrade);

        $tableMapWriter = new TableMapWriter();

        foreach($tablesToUpgrade as $knownTable){
            /** @var $knownTable \Intahwebz\TableMap\TableMap */
            $tableMapWriter->generateObjectFile(
                $knownTable,
                realpath(__DIR__)."/DTO/",
                'Intahwebz\\TableMap\\Tests\\DTO'
            );
        }
    }

    function setUp() {
        $this->provider = createProvider();
        $this->sqlQueryFactory = $this->provider->make('Intahwebz\TableMap\SQLQueryFactory');

        $sqlQuery = $this->sqlQueryFactory->create();

        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $contentDTO = new Intahwebz\TableMap\Tests\DTO\MockContentDTO;
        $insertID = $sqlQuery->insertIntoMappedTable($table, $contentDTO);
        $this->assertGreaterThan(0, $insertID, "Insert failed?");

        $insertID = $sqlQuery->insertIntoMappedTable($table, $contentDTO);
        $this->assertNotNull($insertID, "Insert is null");
        
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');

        $data = [
            'mockContentID' => 1,
            'title' => "My first Note",
            'text' => "Deep v 3 wolf moon bitters, ugh gluten-free disrupt pickled kale chips Banksy tattooed hella tofu Intelligentsia. Trust fund deep v PBR literally, 8-bit paleo DIY Odd Future cornhole polaroid try-hard hella butcher single-origin coffee sriracha. DIY mixtape irony messenger bag, tattooed banjo bicycle rights. Raw denim meggings pour-over bitters. Pour-over forage seitan lomo bicycle rights, flexitarian organic. Hoodie actually gluten-free VHS YOLO, polaroid Bushwick Pinterest Neutra Intelligentsia synth pour-over master cleanse fanny pack fingerstache. Organic keffiyeh trust fund, deep v ugh flexitarian narwhal freegan tofu.",
        ];
        $sqlQuery->insertIntoMappedTable($table, $data);
    }

    function testSingleObject() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
        $sqlQuery->table($table)->whereColumn('mockNoteID', 1);

        $objects = $sqlQuery->fetchObjects();
        
        $this->assertCount(1, $objects);
        
        $this->assertInstanceOf(
            'Intahwebz\TableMap\Tests\DTO\MockNoteDTO', 
            $objects[0]
        );
    }

    function testSingleCompositedObject() {
        $sqlQuery = $this->sqlQueryFactory->create();
        $contentTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockContentSQLTable');
        $mockNoteTable = $this->provider->make('Intahwebz\TableMap\Tests\Table\MockNoteSQLTable');
        $sqlQuery->table($contentTable);
        $sqlQuery->table($mockNoteTable)->whereColumn('mockNoteID', 1);
        $objects = $sqlQuery->fetchObjects();
        $this->assertCount(1, $objects);
        
        $this->assertInstanceOf(
            'Intahwebz\\TableMap\\Tests\\DTO\\MockContentDTOXMockNoteDTO',
            $objects[0]
        );
    }    
}
 