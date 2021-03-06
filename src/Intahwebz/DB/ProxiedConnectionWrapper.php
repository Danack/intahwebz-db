<?php


namespace Intahwebz\DB;

use Psr\Log\LoggerInterface;

class ProxiedConnectionWrapper implements Connection {

    /**
     * @var Connection
     */
    private $instance = null;

    /**
     * @var StatementFactory
     */
    private $statementWrapperFactory;

    private $host;
    private $username;
    private $password;
    private $port;
    private $socket;
    
    function __construct(LoggerInterface $logger, 
                         StatementFactory $statementWrapperFactory,
                         $host, $username, $password, $port, $socket) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->socket = $socket;
        $this->logger = $logger;
        $this->statementWrapperFactory = $statementWrapperFactory;
    }

    function checkInstance() {
        if ($this->instance == null) {
            $this->instance = new MySQLiConnection(
                $this->logger,
                $this->statementWrapperFactory,
                $this->host,
                $this->username,
                $this->password,
                $this->port,
                $this->socket
            );
        }
    }

    function activateTransaction() {
        $this->checkInstance();
        return $this->instance->activateTransaction();
    }

    function commit() {
        $this->checkInstance();
        return $this->instance->commit();
    }

    function close($closeCached = FALSE){
        $this->checkInstance();
        return $this->instance->close($closeCached);
    }

    function rollback() {
        $this->checkInstance();
        return $this->instance->rollback();
    }

    function prepareStatement($queryString, $log = FALSE, $callstackLevel = 0) {
        $this->checkInstance();
        return $this->instance->prepareStatement($queryString, $log, $callstackLevel);
    }

    function prepareAndExecute($queryString, $log = FALSE) {
        $this->checkInstance();
        return $this->instance->prepareAndExecute($queryString, $log);
    }

    function directExecute($queryString) {
        $this->checkInstance();
        return $this->instance->directExecute($queryString);
    }

    function getLastError() {
        $this->checkInstance();
        return $this->instance->getLastError();
    }

    function selectSchema($schema) {
        $this->checkInstance();
        return $this->instance->selectSchema($schema);
    }
    
}

