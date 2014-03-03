<?php


namespace Intahwebz\DBSync\Tests;

class DBSyncTest extends \PHPUnit_Framework_TestCase {


    /**
     * @var \Auryn\Provider
     */
    private $provider;

    static function setUpBeforeClass() {
    }

    protected function setUp(){

        $provider = createProvider([]);
        $dbConnection = $provider->make('Intahwebz\DB\Connection');

        /**
         * @var $dbConnection \Intahwebz\DB\Connection
         */
        $query = "DROP SCHEMA IF EXISTS mocks;";
        $dbConnection->prepareAndExecute($query);

        $mocks = [
        ];

        $this->provider = createProvider($mocks);
    }

    protected function tearDown(){
    }

    public function testBasic() {

        /** @var $dbSync \Intahwebz\DBSync\DBSync */
        $dbSync = $this->provider->make('Intahwebz\DBSync\DBSync');


        $tablesToUprade = [
            new \Intahwebz\DBSync\Tests\MockContentSQLTable(),
            new \Intahwebz\DBSync\Tests\MockNoteSQLTable(),
            new \Intahwebz\DBSync\Tests\MockHashSQLTable(),
        ];

        $dbSync->processUpgradeForSchema('mocks', $tablesToUprade);
    }

    public function testFields() {
        /** @var $dbSync \Intahwebz\DBSync\DBSync */
        $dbSync = $this->provider->make('Intahwebz\DBSync\DBSync');

        $tablesToUprade = [
            new \Intahwebz\DBSync\Tests\MockFieldChangeBeforeTable(),
        ];

        $dbSync->processUpgradeForSchema('mocks', $tablesToUprade);

        /** @var $dbSync \Intahwebz\DBSync\DBSync */
        $dbSync = $this->provider->make('Intahwebz\DBSync\DBSync');

        $tablesToUprade = [
            new \Intahwebz\DBSync\Tests\MockFieldChangeAfterTable(),
        ];

        $dbSync->processUpgradeForSchema('mocks', $tablesToUprade);
    }




}



