<?php
/**
 * Created by PhpStorm.
 * User: dilsonrabelo
 * Date: 23/01/17
 * Time: 13:49
 */

namespace Slim3\Log;


use Monolog\Logger;
use Slim\App;

class SlimMagicMonologFacade
{
    /**
     * @var Logger
     */
    private static $logger;

    public static function initialize(App $app, array $settings = []) {
        $ci = $app->getContainer();

        $classFactory = new $settings['Factory'];
        self::$logger = $classFactory($ci);

        $app->add(new LogMiddleware());
    }

    public static function info($message)
    {
        self::$logger->info($message);
    }

    public static function error($message)
    {
        self::$logger->error($message);
    }

    public static function warning($message)
    {
        self::$logger->warning($message);
    }

    public static function debug($message)
    {
        self::$logger->debug($message);
    }

    public static function notice($message)
    {
        self::$logger->notice($message);
    }

    public static function critical($message)
    {
        self::$logger->critical($message);
    }

    public static function alert($message)
    {
        self::$logger->alert($message);
    }

    public static function emergency($message)
    {
        self::$logger->emergency($message);
    }

    public static function getLogger()
    {
        return self::$logger;
    }

    public static function writeLogger($level = Logger::DEBUG, $message) {
        self::$logger->addRecord($level, $message);
    }
}