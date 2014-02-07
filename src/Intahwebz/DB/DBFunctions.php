<?php

namespace Intahwebz\DB {


class DBFunctions {
    static function load(){}
}

}

namespace {

use Intahwebz\DB;
use Intahwebz\DB\Connection;
use Intahwebz\Exception\UnsupportedOperationException;

/* Generates a query snippet.
 *
 * e.g. given $columnName = message
 * $plainTextSearchArray = array('Dog', 'cat', 'bat)
 * return "message like ? or message like ? or message like ?"
 */
function getLikeQueryForMatching($columnName, $plainTextSearchArray){

    $likeQueryForMatching = "";
    $orString = '';

    /** @noinspection PhpUnusedLocalVariableInspection */
    foreach($plainTextSearchArray as $plainTextSearch){
        $likeQueryForMatching .= $orString;
        $likeQueryForMatching .= "$columnName like ?";

        $orString = ' or ';
    }

    return $likeQueryForMatching;
}


function getNowMySQLTime(){

    $dateTest  = mktime();

    $dateString = date("Y-m-d H:i:s", $dateTest);

    return $dateString;
}


function getOffsetTime($supportedTimeZone) {
    $dateTime = new DateTime();
    $serverOffsetTime = $dateTime->getOffset();
    $dateTimeZone = new DateTimeZone($supportedTimeZone);
    $dateTime->setTimezone($dateTimeZone);
    $offsetTime = $dateTime->getOffset();

    return $offsetTime - $serverOffsetTime;
}


function getDayNumberForTimezone(Connection $dbConnection, $timezone){
    $offsetTime = getOffsetTime($timezone);

    return getDayNumberWithOffset($dbConnection, $offsetTime);
}


function getDayNumberWithOffset(Connection $dbConnection, $secondsOffset) {

    $queryString = "SELECT TO_DAYS(DATE_ADD( now(), INTERVAL ? SECOND ));";

    $connectionWrapper = $dbConnection;
    $statementWrapper = $connectionWrapper->prepareStatement($queryString);
    $statementWrapper->statement->bind_param('i', $secondsOffset);
    $statementWrapper->execute();

    $dateNumber = false;
    $statementWrapper->statement->bind_result($dateNumber);

    if($statementWrapper->statement->fetch()) {
        $statementWrapper->close();
        return	$dateNumber;
    }

    $statementWrapper->close();
    return FALSE;
}

function getMysqlTimeWithOffset(Connection $dbConnection, $secondsOffset) {

    $queryString = "select date_add(now(), interval ? second) as datetime;;";
    $connectionWrapper = $dbConnection;

    $statementWrapper = $connectionWrapper->prepareStatement($queryString);
    $statementWrapper->statement->bind_param('i', $secondsOffset);
    $statementWrapper->execute();

    $datetime = false;
    $statementWrapper->statement->bind_result($datetime);

    if($statementWrapper->statement->fetch()) {
        $statementWrapper->close();
        return	$datetime;
    }

    $statementWrapper->close();
    return FALSE;
}


/* This function gets the current day for a user.
 *	They may be so far behind the server timezone that they
 * are in the past.
 */

function	getDaysAgoFromOffsetTime($offsetSeconds, Connection $dbConnection){

    $offsetSecondsInt = intval($offsetSeconds);

    $queryString = "select DATEDIFF(NOW(), DATE_ADD(NOW(), INTERVAL $offsetSecondsInt SECOND)) as daysAgo;";
    $connectionWrapper = $dbConnection;
    $statementWrapper = $connectionWrapper->prepareAndExecute($queryString);

    $datetime = false;
    $statementWrapper->statement->bind_result($datetime);

    if($statementWrapper->statement->fetch()) {
        $statementWrapper->close();
        return	$datetime;
    }

    $statementWrapper->close();
    return FALSE;

}

function getDayWeek(Connection $dbConnection){

    $queryString = "select DAYOFWEEK(now()) as dayWeek;";
    $connectionWrapper = $dbConnection;
    $statementWrapper = $connectionWrapper->prepareAndExecute($queryString);

    $dayOfWeek = false;
    $statementWrapper->statement->bind_result($dayOfWeek);

    if($statementWrapper->statement->fetch()) {
        $statementWrapper->close();

        switch($dayOfWeek){
            default:
            case(1):{ return 'sunday';      }
            case(2):{ return 'monday';      }
            case(3):{ return 'tuesday';     }
            case(4):{ return 'wednesday';   }
            case(5):{ return 'thursday';    }
            case(6):{ return 'friday';      }
            case(7):{ return 'saturday';    }
        }
    }

    $statementWrapper->close();
    return 'sunday';
}

function getDayWeekforDatetime($datetime, Connection $dbConnection){

    $queryString = "select DAYOFWEEK(?) as dayWeek;";
    $connectionWrapper = $dbConnection;

    $statementWrapper = $connectionWrapper->prepareStatement($queryString);
    $statementWrapper->statement->bind_param('s', $datetime);
    $statementWrapper->execute();

    $dayOfWeek = false;
    $statementWrapper->statement->bind_result($dayOfWeek);

    if($statementWrapper->statement->fetch()) {
        $statementWrapper->close();

        switch($dayOfWeek){
            default:
            case(1):{ return 'sunday';      }
            case(2):{ return 'monday';      }
            case(3):{ return 'tuesday';     }
            case(4):{ return 'wednesday';   }
            case(5):{ return 'thursday';    }
            case(6):{ return 'friday';      }
            case(7):{ return 'saturday';    }
        }
    }

    $statementWrapper->close();
    return 'sunday';
}


function getInString($array) {
    $inString = NULL;

    if(is_array($array) != TRUE){
        throw new UnsupportedOperationException("Exception in getInString, you must pass in an array to be converted into a string.");
    }

    foreach($array as $element){

        $pattern = '/[^0-9]*/';
        $value = preg_replace($pattern, '', $element);

        if($inString == NULL){
            $inString = "".$value;
        }
        else{
            $inString .= ",".$value;
        }
    }

    return $inString;
}

function getSQLStatus(Connection $dbConnection){

    $connectionWrapper = $dbConnection;

//	$queryString = "show global status
//							 where Variable_name in (
//
//							'Com_insert',
//							'Com_delete',
//							'Com_select',
//							'Com_update' );";

    $queryString = "show global status";

    $statementWrapper = $connectionWrapper->prepareAndExecute($queryString);

    $variableName = $value = false;

    $statementWrapper->statement->bind_result($variableName, $value);

    $sqlInfoArray = array();

    while($statementWrapper->statement->fetch()){
        $sqlInfoArray[$variableName] = $value;
    }

    $statementWrapper->close();

    return $sqlInfoArray;
}


function getInnoDBStatus(Connection $dbConnection){

    $connectionWrapper = $dbConnection;

    $queryString = "show engine innodb status;";

    $mysqliResult = $connectionWrapper->directExecute($queryString);

    $results = $mysqliResult->fetch_array();

    return $results[2];//TODO - Only a little bit fragile.
}


function	getDaysAgoStartAndEnd($dateStart, $dateEnd, Connection $dbConnection) {

    $connectionWrapper = $dbConnection;

    $queryString = "select
                    datediff(now(), ?) as daysAgoStart,
                    datediff(now(), ?) as daysAgoEnd";

    $statementWrapper = $connectionWrapper->prepareStatement($queryString);

    $statementWrapper->statement->bind_param( 'ss',
                                                $dateStart,
                                                $dateEnd);

    $statementWrapper->execute();

    $daysAgoStart = $daysAgoEnd = false;

    $statementWrapper->statement->bind_result(  $daysAgoStart,
                                                $daysAgoEnd);

    $statementWrapper->statement->fetch();
    $statementWrapper->close();

    $daysAgoInfo = array();
    $daysAgoInfo['daysAgoStart'] = $daysAgoStart;
    $daysAgoInfo['daysAgoEnd'] = $daysAgoEnd;

    return $daysAgoInfo;
}


function	findUnmappedCharacters(DB\Connection $dbConnection, $lastDate = FALSE){

    $queryString = "select  outqueueID,
                            message,
                            CAST(message AS char CHARACTER SET latin1)
                    from premiumplatform.outqueue
                    where (message != CAST(message AS char CHARACTER SET latin1)) ";

    $connectionWrapper = $dbConnection;

    if($lastDate == FALSE){
        $statementWrapper = $connectionWrapper->prepareAndExecute($queryString);
    }
    else{
        $queryString .=  " and datestamp > ?;";
        $statementWrapper = $connectionWrapper->prepareStatement($queryString);
        $statementWrapper->statement->bind_param( 's', $lastDate);
        $statementWrapper->execute();
    }

    $outqueueID = $message = $convertedMessage = '';

    $statementWrapper->statement->bind_result($outqueueID, $message, $convertedMessage);

    $resultsArray = array();

    while($statementWrapper->statement->fetch()){
        $result = array();
        $result['outqueueID'] = $outqueueID;
        $result['message'] = $message;
        $result['convertedMessage'] = $convertedMessage;

        $resultsArray[] = $result;
    }

    return	$resultsArray;
}

use Intahwebz\TableMap\TableMap;

    function getRelationshipTable(TableMap $firstTable, TableMap $secondTable) {

        $className = sprintf(
            '%sX%sX%sRelation',
            $firstTable->getTableName(),
            $secondTable->getTableName(),
            "foo" //$secondTable->relationName
        );

        $namespace = getNamespace($firstTable);
        $namespaceClassName = $namespace."\\".$className;

        return new $namespaceClassName();
    }


}
