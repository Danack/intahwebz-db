<?php


namespace Intahwebz\TableMap\Tests;

use Intahwebz\TableMap\SQLQueryFactory;
use Intahwebz\TableMap\Tests\DTO\UserTableDTO;


class EmailUserMapper {

    /**
     * @var \Intahwebz\TableMap\SQLQueryFactory
     */
    private $sqlQueryFactory;

    private $userTable;
    private $emailSQLTable;
    private $emailUserJoinTable;

    function __construct(
        SQLQueryFactory $sqlQueryFactory,
        \Intahwebz\TableMap\Tests\UserTable $userTable,
        \Intahwebz\TableMap\Tests\EmailSQLTable $emailSQLTable,
        \Intahwebz\TableMap\Tests\EmailUserJoinTable $emailUserJoinTable
        ) {

        $this->userTable = $userTable;
        $this->emailSQLTable = $emailSQLTable;
        $this->emailUserJoinTable = $emailUserJoinTable;
    }

//    function addUserWithEmails($userDTO, array $emails){
//
//        $sqlQuery = $this->sqlQueryFactory->create();
//
//        $sqlQuery->insertIntoMappedTable($this->userTable);
//
//        return $imageData;
//    }


    function addUser(UserTableDTO $userDTO) {
        $sqlQuery = $this->sqlQueryFactory->create();

        return $sqlQuery->insertIntoMappedTable($this->userTable, $userDTO);
    }
}

 