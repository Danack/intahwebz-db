<?php

namespace Intahwebz\TableMap\Tests\DTO;

class MockHashSQLTableDTO {
	public $username;
	public $password;

	public function __construct($username = null, $password = null) {
		$this->username = $username;
		$this->password = $password;
	} 
	function username($username) { 
		$this->username = $username;
	}

	function password($password) { 
		$this->password = $password;
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


