<?php

namespace Intahwebz\TableMap\Tests\DTO;

class EmailUserJoinTableDTO {
	public $emailID;
	public $userID;

	public function __construct($emailID = null, $userID = null) {
		$this->emailID = $emailID;
		$this->userID = $userID;
	} 
	function emailID($emailID) { 
		$this->emailID = $emailID;
	}

	function userID($userID) { 
		$this->userID = $userID;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $emailUserJoinTable \Intahwebz\TableMap\Tests\Table\EmailUserJoinTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\Table\EmailUserJoinTable $emailUserJoinTable){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($emailUserJoinTable, $data);

        return $insertID;
    }
}


