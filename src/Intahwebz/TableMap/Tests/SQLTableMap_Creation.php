<?php

asdasds

class SQLTableMap_Creation extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Auryn\Provider
     */
    private $provider;

    static function setUpBeforeClass() {

        $mocks = [
        ];

        $provider = createProvider($mocks);

        //This dumps all tables
        $dbSync = $provider->make(\Intahwebz\DBSync\DBSync::class);
        $dbSync->processUpgradeForSchema('mocks', []);
    }

    function setUp() {
        $mocks = [
            //    'Intahwebz\Session' => 'Intahwebz\Session\MockSession'
        ];

        $this->provider = createProvider($mocks);
        //$this->sqlQueryFactory = $this->provider->make(Intahwebz\TableMap\SQLQueryFactory::class);
    }
    
    asdsd

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
        
        echo "lol qut?";
        
        
        foreach($tablesToUprade as $knownTable){
            /** @var $knownTable \Intahwebz\TableMap\TableMap */
            $knownTable->generateObjectFile(
                realpath(__DIR__)."/DTO/",
                "DTO.php",
                'Intahwebz\\TableMap\\Tests\\DTO'
            );
        }
    }


}
 