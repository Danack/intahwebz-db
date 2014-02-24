<?php



use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;

use Auryn\Provider;


function createStandardLogger($logChannelName = 'logchannelname') {
    $logger = new Logger($logChannelName);
    $pid = getmypid();

    $standardFormat = "[%datetime%] $pid %channel%.%level_name%: %message% %context% %extra%\n";
    $formatter = new \Monolog\Formatter\LineFormatter($standardFormat);

    $streamInfoHandler = new StreamHandler('./var/log/mono.log', Logger::INFO);
    $streamInfoHandler->setFormatter($formatter);
    //wrap the stream handler with a fingersCrossedHandler.
    $fingersCrossedHandler = new FingersCrossedHandler(
        $streamInfoHandler,
        new ErrorLevelActivationStrategy(Logger::WARNING),
        $bufferSize = 0,
        $bubble = true,
        $stopBuffering = true
    );

    $logger->pushHandler($fingersCrossedHandler);    //Push the handler to the logger.

    return $logger;
}

function createProvider($mocks = array(), $shares = array()) {

    \Intahwebz\Functions::load();
    \Intahwebz\MBExtra\Functions::load();

    $standardImplementations = [
        'Intahwebz\DB\Connection' => 'Intahwebz\DB\ProxiedConnectionWrapper',
        'Intahwebz\DB\StatementFactory' => 'Intahwebz\DB\MySQLiStatementFactory'
    ];

    $standardLogger = createStandardLogger();

    $provider = new Provider();
    $provider->alias('Psr\Log\LoggerInterface', 'Intahwebz\Logger\NullLogger');


    $dbParams = array(
        ':host'     => MYSQL_SERVER,
        ':username' => MYSQL_USERNAME,
        ':password' => MYSQL_PASSWORD,
        ':port'     => MYSQL_PORT,
        ':socket'   => MYSQL_SOCKET_CONNECTION
    );

    $provider->define(
        'Intahwebz\DB\MySQLiConnection',
        $dbParams
    );
    $provider->define(
        'Intahwebz\DB\ProxiedConnectionWrapper',
        $dbParams
    );

    $provider->alias('Intahwebz\ObjectCache', 'Intahwebz\Cache\APCObjectCache');

    foreach ($standardImplementations as $interface => $implementation) {
        if (array_key_exists($interface, $mocks)) {
            if (is_object($mocks[$interface]) == true) {
                $provider->alias($interface, get_class($mocks[$interface]));
                $provider->share($mocks[$interface]);
            }
            else {
                $provider->alias($interface, $mocks[$interface]);
            }
            unset($mocks[$interface]);
        }
        else {
            $provider->alias($interface, $implementation);
        }
    }

    foreach ($mocks as $class => $implementation) {
        if (is_object($implementation) == true) {
            $provider->alias($class, get_class($implementation));
            $provider->share($implementation);
        }
        else {
            $provider->alias($class, $implementation);
        }
    }

    $stanardShares = [];

    foreach ($stanardShares as $class => $share) {
        if (array_key_exists($class, $shares)) {
            $provider->share($shares[$class]);
            unset($shares[$class]);
        }
        else {
            $provider->share($share);
        }
    }

    foreach ($shares as $class => $share) {
        $provider->share($share);
    }

    $provider->share($provider);

    return $provider;
}

 