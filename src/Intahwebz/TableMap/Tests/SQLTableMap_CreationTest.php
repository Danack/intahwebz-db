<?php

use Intahwebz\TableMap\TableMapWriter;

class SQLTableMap_CreationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Auryn\Provider
     */
    private $provider;

    static function setUpBeforeClass() {

        $mocks = [
        ];

        $provider = createProvider($mocks);

        //This dumps all tables
        $dbSync = $provider->make('\Intahwebz\DBSync\DBSync');
        $dbSync->processUpgradeForSchema('mocks', []);
    }

    function setUp() {
        $mocks = [
            //    'Intahwebz\Session' => 'Intahwebz\Session\MockSession'
        ];

        $this->provider = createProvider($mocks);
        //$this->sqlQueryFactory = $this->provider->make(Intahwebz\TableMap\SQLQueryFactory::class);
    }
    
    function testTableCreation() {

        $tablesToUprade = [
            new Intahwebz\TableMap\Tests\MockContentSQLTable(),
            new Intahwebz\TableMap\Tests\MockNoteSQLTable(),
            new Intahwebz\TableMap\Tests\MockHashSQLTable(),
            new Intahwebz\TableMap\Tests\MockRandDataSQLTable(),
            new Intahwebz\TableMap\Tests\MockCommentSQLTable(),
            new Intahwebz\TableMap\Tests\MockCommentTreePathSQLTable(),
        ];

        /** @var $dbSync Intahwebz\DBSync\DBSync */
        $dbSync = $this->provider->make(Intahwebz\DBSync\DBSync::class);
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


}
 