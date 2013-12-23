<?php

namespace Intahwebz\DBSync;

//TODO - this is all dead, but needs to be brought back.



define('NL', "\r\n");
define('SKIP_MYSQL_LOGGING', TRUE);
define("COMMAND_LINE", TRUE);
//
//define('OPERATION_TYPE_INDEX', 'OPERATION_TYPE_INDEX');
//define('OPERATION_TYPE_COLUMN', 'OPERATION_TYPE_COLUMN');
//define('OPERATION_TYPE_TABLE', 'OPERATION_TYPE_TABLE');

$GLOBALS['operationPriorities'] = array(
	OPERATION_TYPE_INDEX => 2,
	OPERATION_TYPE_COLUMN => 1,
	OPERATION_TYPE_TABLE => 0,
);

$GLOBALS['count'] = 0;
$GLOBALS['countStart'] = 0; //Edit this one to skip over tasks that are already run

require_once('sqlTask.php.inc');

main();
exit(0);
//end of program.

function main(){

	if($GLOBALS['argc'] != 2){
		echo "Please say which action you wish to take e.g. \r\n";
		echo "php release.php [backup|upgrade|rollback] \r\n";
		exit(0);
	}

	$action = $GLOBALS['argv'][1];

	$availableActions = array(
		'backup',
		'upgrade',
		'rollback',
	);

	if(in_array($action, $availableActions) == FALSE){
		echo "Action [$action] is not an available please choose one of backup, upgrade or rollback.";
		exit(0);
	}

	try{
		processDatabaseOperationsForAction($action);
	}
	catch(\Exception $e){

		echo "Exception processing upgrade: ".$e->getMessage();
		echo "\r\n\r\n";

		echo "Upgrade blew up on count ".$GLOBALS['count']."\r\n";
		echo "Set \$GLOBALS['countStart'] to this value to restart on that command\r\n";
	}
}


function	compareOperations($a, $b){

	if(isset($a['operationType']) == FALSE){
		echo "Operation 'a' does not have operationType set, cannot continue.";
		var_dump($a);
		exit(0);
	}

	if(isset($b['operationType']) == FALSE){
		echo "Operation 'b' does not have operationType set, cannot continue.";
		var_dump($a);
		exit(0);
	}

	if(isset($GLOBALS['operationPriorities'][$a['operationType']]) == FALSE){
		echo "Operation 'a' has unknown operationType [".$a['operationType']."].";
		exit(0);
	}

	if(isset($GLOBALS['operationPriorities'][$b['operationType']]) == FALSE){
		echo "Operation 'b' has unknown operationType [".$b['operationType']."].";
		exit(0);
	}

	return $GLOBALS['operationPriorities'][$a['operationType']] > $GLOBALS['operationPriorities'][$b['operationType']];
}




function	processDatabaseOperationsForAction($action){

	$actionsToTake = $GLOBALS['databaseOperations'];

	usort($actionsToTake, 'compareOperations');

	if($action == 'rollback'){
		$actionsToTake = array_reverse($actionsToTake);
	}

	//$connectionWrapper = connectToDB();

	foreach($actionsToTake as $databaseOperation){

		$sqlString = $databaseOperation[$action];

		if($sqlString != FALSE){

			echo $GLOBALS['count'].": ";
			if($GLOBALS['count'] < $GLOBALS['countStart']){
				echo "Skipping [ $sqlString ] which is of type ".$databaseOperation['operationType']."\r\n";
			}
			else{
				echo "Do [ $sqlString ] which is of type ".$databaseOperation['operationType']."\r\n";
				//$statementWrapper = $connectionWrapper->prepareAndExecute($sqlString);
				//$statementWrapper->close();
			}
		}

		$GLOBALS['count']++;
	}

	//$connectionWrapper->close();
}




