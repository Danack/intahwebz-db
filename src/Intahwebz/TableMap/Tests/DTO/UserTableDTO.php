<?php

namespace Intahwebz\TableMap\Tests\DTO;

class UserTableDTO {
	public $userID;
	public $datestamp;
	public $firstName;
	public $lastName;

	public function __construct($userID = null, $datestamp = null, $firstName = null, $lastName = null) {
		$this->userID = $userID;
		$this->datestamp = $datestamp;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
	} 
	function userID($userID) { 
		$this->userID = $userID;
	}

	function datestamp($datestamp) { 
		$this->datestamp = $datestamp;
	}

	function firstName($firstName) { 
		$this->firstName = $firstName;
	}

	function lastName($lastName) { 
		$this->lastName = $lastName;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $userTable \Intahwebz\TableMap\Tests\UserTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\UserTable $userTable){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($userTable, $data);
	$this->userID = $insertID;

        return $insertID;
    }
}


