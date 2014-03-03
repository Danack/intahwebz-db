<?php

namespace Intahwebz\TableMap\Fragment;


use Intahwebz\TableMap\SQLQuery;

class  SQLFragment{

    use \Intahwebz\SafeAccess;

    function __construct(){
    }

    
    function joinBit(SQLQuery $sqlQuery) {}
    function onBit(SQLQuery $sqlQuery) {}
    function randBit(SQLQuery $sqlQuery, &$tableMap){}
    function whereBit(SQLQuery $sqlQuery) {}
    
//    function rand(SQLQuery $sqlQuery) {
//        
//    }
}


