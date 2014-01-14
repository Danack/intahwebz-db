<?php

use Intahwebz\DataPath;
use Intahwebz\StoragePath;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;

use Intahwebz\Logger\NullLogger;
use Auryn\Provider;

error_reporting(E_ALL);

require_once('../config.php');

//define('PATH_TO_ROOT', './');

require_once('./vendor/autoload.php');

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
    //$logger->pushHandler($streamInfoHandler);

    return $logger;
}




function createProvider($mocks = array(), $shares = array()) {

    \Intahwebz\Functions::load();
    \Intahwebz\MBExtra\Functions::load();
    //\BaseReality\Functions::load();

    $standardImplementations = [
        //'Intahwebz\Session' => Intahwebz\Session\MockSession::class,
        //'Intahwebz\Storage\Storage' => Intahwebz\Storage\S3Storage::class,
        'Intahwebz\DB\Connection' => Intahwebz\DB\ProxiedConnectionWrapper::class,
        Intahwebz\DB\StatementFactory::class => Intahwebz\DB\MySQLiStatementFactory::class
        //'Intahwebz\Router' => Intahwebz\Routing\Router::class,
        //'Intahwebz\FileFetcher' => Intahwebz\Utils\UploadedFileFetcher::class,
        //'Intahwebz\Request' => Intahwebz\Routing\HTTPRequest::class,
        //'Intahwebz\Response' => Intahwebz\Routing\HTTPResponse::class,
        //'Intahwebz\Domain' => BaseReality\DomainWWW::class
    ];

    $standardLogger = createStandardLogger();

    $provider = new Provider();
    $provider->alias('Psr\Log\LoggerInterface', Intahwebz\Logger\NullLogger::class);


    $dbParams = array(
        ':host'     => MYSQL_SERVER,
        ':username' => MYSQL_USERNAME,
        ':password' => MYSQL_PASSWORD,
        ':port'     => MYSQL_PORT,
        ':socket'   => MYSQL_SOCKET_CONNECTION
    );

    $provider->define(
             \Intahwebz\DB\MySQLiConnection::class,
             $dbParams
    );
    $provider->define(
             \Intahwebz\DB\ProxiedConnectionWrapper::class,
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

        $stanardShares = [
//            'BaseReality\AutogenPath' => new AutogenPath(PATH_TO_ROOT."autogen/"),
//            'Intahwebz\Jig\JigConfig' => new JigConfig(
//                    PATH_TO_ROOT."templates/phpbasereality/",
//                    PATH_TO_ROOT."var/src/",
//                    "tpl",
//                    \Intahwebz\Jig\JigRender::COMPILE_ALWAYS
//                )
        ];

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

//        $provider->alias('Intahwebz\Router', Intahwebz\Routing\Router::class);
//        $provider->alias('Intahwebz\ViewModel', 'BaseReality\View\BaseRealityPHPView');
//        $provider->alias('BaseReality\View\BaseRealityPHPView', 'BaseReality\View\CachingBaseRealityPHPView');

//        $provider->share('Intahwebz\Router');
//        $provider->share('Intahwebz\DB\DBConnection');
//        $provider->share('BaseReality\View\BaseRealityPHPView');
//
//        //$imagesPerPage = $siteConfig->getVariable('imagesPerPage', 40);
//        $provider->delegate(
//                 BaseReality\Model\ContentFilter::class,
//                 'getContentFilter',
//                 [':itemsPerPage' => 40]
//        );
//
//
//
//        $provider->define(
//                 Intahwebz\Routing\HTTPRequest::class,
//                 array(
//                     ':server' => $_SERVER,
//                     ':get' => $_GET,
//                     ':post' => $_POST,
//                     ':files' => $_FILES,
//                     ':cookie' => $_COOKIE
//                 )
//        );

//        $provider->share('Intahwebz\Request');
//        $request = $provider->make('Intahwebz\Request');

        $provider->share($provider);

//        $extension = "tpl";
//
//        $hostname = $request->getHostName();

//        $cdnAvailable = true;
//
//        $routerParams = array(
//            ':routeCollectionName' => 'basereality.com',
//            ':pathToRouteInfo' => PATH_TO_ROOT."data/routing/basereality.com.php"
//        );

//        $provider->define(\Intahwebz\Routing\Router::class, $routerParams);

//        $provider->define(
//            'Intahwebz\Utils\ScriptInclude', [
//                ':packScripts' => false, //$siteConfig->getVariable('packScripts', false),
//                ':useCDNForScripts' => $cdnAvailable
//            ]
//        );

//        $router = $provider->make('Intahwebz\Router');
//        /** @var  $router \Intahwebz\Router */
//
//        $accessControl = $provider->make('BaseReality\Security\AccessControl');
//        $session = $provider->make('Intahwebz\Session');

//        $defaultJigConfig = new JigConfig(
//            PATH_TO_ROOT."templates/phpbasereality/",
//            PATH_TO_ROOT."var/src/",
//            $extension,
//            \Intahwebz\Jig\JigRender::COMPILE_ALWAYS
//        );
//
//        $cssJigConfig = new JigConfig(
//            PATH_TO_ROOT."templates/phpbasereality/",
//            PATH_TO_ROOT."var/src/",
//            'tpl.css',
//            \Intahwebz\Jig\JigRender::COMPILE_CHECK_MTIME
//        );
//
        //$provider->share($defaultJigConfig);
//        $provider->share($cssJigConfig, array(
//                                          'Intahwebz\CSSGenerator\CSSGenerator'
//                                      ));
//
//        //This is because All the content is not router aware.
//        setRouter($router);
//
//        $session->initSession(SESSION_NAME);

//        $view = $provider->make('BaseReality\View\BaseRealityPHPView');

    return $provider;
}

 