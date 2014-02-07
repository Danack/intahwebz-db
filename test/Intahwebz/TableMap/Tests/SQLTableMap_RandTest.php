<?php

use Intahwebz\TableMap\SQLQueryFactory;
use Intahwebz\TableMap\TableMapWriter;

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

class SQLTableMap_RandTest extends \PHPUnit_Framework_TestCase {

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
            //    'Intahwebz\Session' => 'Intahwebz\Session\MockSession'
        ];

        $provider = createProvider($mocks);


        //This dumps all tables
        $dbSync = $provider->make(\Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', []);

        $tablesToUprade = [
            new Intahwebz\TableMap\Tests\Table\MockRandDataSQLTable(),
        ];

        /** @var $dbSync Intahwebz\DBSync\DBSync */
        $dbSync = $provider->make(Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', $tablesToUprade);

        $tableMapWriter = new TableMapWriter();


        foreach($tablesToUprade as $knownTable){
            /** @var $knownTable \Intahwebz\TableMap\TableMap */
            $tableMapWriter->generateObjectFile(
                $knownTable,
                realpath(__DIR__)."/DTO/",
                'Intahwebz\\TableMap\\Tests\\DTO'
            );
        }
    }

    function setUp() {
        $mocks = [
        ];

        $this->provider = createProvider($mocks);
        $this->sqlQueryFactory = $this->provider->make(Intahwebz\TableMap\SQLQueryFactory::class);
    }

    function testRandSelect() {

        $sqlQuery = $this->sqlQueryFactory->create();
        $table = $this->provider->make(Intahwebz\TableMap\Tests\Table\MockRandDataSQLTable::class);
       
        for ($x=0 ; $x<50 ; $x++) {
            $data['title'] = generateRandomString(10);
            $data['text'] = generateRandomString(50);
            $sqlQuery->insertIntoMappedTable($table, $data);
        }

        for ($x=0 ; $x<60 ; $x++) {
            $sqlQuery = $this->sqlQueryFactory->create();
            $table = $this->provider->make(Intahwebz\TableMap\Tests\Table\MockRandDataSQLTable::class);
            $sqlQuery->table($table)->rand();
            $result = $sqlQuery->fetch();

            $this->assertEquals(1, count($result));
            //TODO - how to test random results.
            //echo $result[0]['mockRandData.mockRandDataID'].", ";
        }
    }



}
 