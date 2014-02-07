<?php

namespace Intahwebz\TableMap\Tests\DTO;

class EmailSQLTableDTO {
	public $emailID;
	public $address;

	public function __construct($emailID = null, $address = null) {
		$this->emailID = $emailID;
		$this->address = $address;
	} 
	function emailID($emailID) { 
		$this->emailID = $emailID;
	}

	function address($address) { 
		$this->address = $address;
	}



    /**
     * @param $query \Intahwebz\TableMap\SQLQuery
     * @param $emailSQLTable \Intahwebz\TableMap\Tests\Table\EmailTable
     * @return int
     */
    function insertInto(\Intahwebz\TableMap\SQLQuery $query, \Intahwebz\TableMap\Tests\Table\EmailTable $emailSQLTable){

        $data = convertObjectToArray($this);
        $insertID = $query->insertIntoMappedTable($emailSQLTable, $data);
	$this->emailID = $insertID;

        return $insertID;
    }
}


