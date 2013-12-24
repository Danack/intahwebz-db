<?php

namespace Intahwebz\TableMap\Tests\DTO;

class MockHashSQLTableDTO {
	public $username;
	public $passwordHash;

	public function __construct($username = null, $passwordHash = null) {
		$this->username = $username;
		$this->passwordHash = $passwordHash;
	} 
	function username($username) { 
		$this->username = $username;
	}

	function passwordHash($passwordHash) { 
		$this->passwordHash = $passwordHash;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $mockHashSQLTable \Intahwebz\TableMap\Tests\MockHashSQLTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\MockHashSQLTable $mockHashSQLTable){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($mockHashSQLTable, $data);

        return $insertID;
    }
}


