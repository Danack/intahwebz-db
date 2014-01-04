<?php

namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;


class MySQLiConnection implements Connection {

    use \Intahwebz\SafeAccess;

    private $forceUTF8Names = false;

    /**
     * @var \mysqli
     */
    private $mysqli;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var StatementFactory
     */
    private $statementWrapperFactory;

    private $MYSQL_PIPE_UNAVAILABLE_COUNT = 0;

    function __construct(
        LoggerInterface $logger, 
        StatementFactory $statementWrapperFactory,
        $host, $username, $password, $port, $socket) {

        //Convert any error to exception?
        //Need to investigate what happens with errors on connection.
        //At the moment we can retry on certain error types - would need to refactor to catch
        //That exception specifically, to allow a retry.
        //mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->logger = $logger;
        $this->statementWrapperFactory = $statementWrapperFactory;

        $finished = false;

        while ($finished == false) {

            try {
                $this->mysqli = @new \mysqli($host, $username, $password, "", $port, $socket);

                $errorNumber = mysqli_connect_errno();

                if ($errorNumber) {
                    throw new DBException('Connect databases failed! error Number is ' . $errorNumber . " error string is " . mysqli_connect_error());
                }
            } catch (\Exception $e) {
                throw new DBException("Exception connecting to database " . $e->getMessage(), 0, $e);
            }

            $errorNumber = \mysqli_connect_errno();

            if ($errorNumber) {

                //if ($db->errno === 1062 /* ER_DUP_ENTRY Duplicate entry - safe for use for detecting duplicates? */)

                if ($errorNumber == 2016 || //# Error: 2016 (CR_NAMEDPIPEWAIT_ERROR) Message: Can't wait for named pipe to host: %s pipe: %s (%lu)
                    $errorNumber == 2017 || //Errorcode: 2017. Reason is Can't open named pipe to host: .  pipe: /tmp/mysql (2)
                    $errorNumber == 2003
                ) {
                    //$sleepDelay = 1;

                    $this->MYSQL_PIPE_UNAVAILABLE_COUNT++;
                    if ($this->MYSQL_PIPE_UNAVAILABLE_COUNT != 0 && ($this->MYSQL_PIPE_UNAVAILABLE_COUNT % 10) == 0) {
                        throw new DBException("MySQL not available.");
                    }

                    //sleep($sleepDelay);
                    usleep(100 * 1000);
                }
                else if ($errorNumber == 2002) {
                    throw new DatabaseMissingException("Database not available.");
                }
                else {
                    throw new DBException("Can't connect to MySQL Server [" . MYSQL_SERVER . "]. Errorcode: " . $errorNumber . ". Reason is " . \mysqli_connect_error() . ". As this reason isn't recognised, terminating application.");
                }
            }
            else {
                //no error, connection should be setup.
                $finished = true;
            }
        }

        if ($this->forceUTF8Names == true) {
            //If the MySQL server does not use UTF8 by default then strings sent to it are sent it whatever
            //is the default character set. That is not what you want. e.g. name = 'dån' as it compares the
            //utf8 column name to the non-UTF8 string.
            $this->directExecute("SET NAMES 'utf8mb4'");
        }
    }


    function activateTransaction() {
        $this->mysqli->autocommit(false);

        return false;
    }

    function commit() {
        $this->mysqli->commit();
        $this->mysqli->autocommit(true);
    }

    function close($closeCached = false) {
        if (false) {
            $this->mysqli->close();
            //$this->open = false;
        }
    }

    function rollback() {
        $this->mysqli->rollback();
        $this->mysqli->autocommit(true);
    }

    function prepareStatement($queryString, $log = false, $callstackLevel = 0) {

        $statement = $this->mysqli->prepare($queryString);

        if ($statement == false) {
            $errorString = "Error preparing statement " . $this->mysqli->error . ". Query was [\n" . $queryString . "\n]";

            $calledFromString = getCalledFromString(1 + $callstackLevel); // 1 is correct for prepared statements prepared through prepareAndExecute.

            throw new DBException($errorString . 'Called from ' . $calledFromString);
        }

        $calledFromString = getCalledFromString(1 + $callstackLevel); // 1 is correct for prepared statements prepared through prepareAndExecute.
        $statementWrapper = $this->statementWrapperFactory->create($statement, $queryString);
        //$statementWrapper->setQueryString($queryString);

        return $statementWrapper;
    }

    function prepareAndExecute($queryString, $log = false) {
        $statementWrapper = $this->prepareStatement($queryString, $log, 1);
        $result = $statementWrapper->execute();

        if ($result === false) {
            throw new DBException("Error preparing statement [" . $this->mysqli->error . "Query was [" . $queryString . "]");
        }

        return $statementWrapper;
    }

    function directExecute($queryString) {
        $result = $this->mysqli->query($queryString);

        if ($result === false) {
            throw new DBException("Error executing sql query [" . $this->mysqli->error . ". Query was [" . $queryString . "]".$this->mysqli->errno);
        }

        return $result;
    }

    function getLastError() {
        if ($this->mysqli == null) {
            return "No connection yet.";
        }
        
        return "".$this->mysqli->errno.":".$this->mysqli->error;
    }

    function selectSchema($schema) {
        return $this->mysqli->select_db($schema);
    }
}
