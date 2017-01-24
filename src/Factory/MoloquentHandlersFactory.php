<?php

namespace Slim3\Log\Factory;


use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Slim3\Log\Handlers\MoloquentHandlers;

class MoloquentHandlersFactory
{
    function __invoke(ContainerInterface $ci)
    {
        $logger = new Logger('SlimMagicMonolog');
        $logger->pushHandler(new MoloquentHandlers($ci->get('settings')->get('SlimMagicMonolog')));
        return $logger;
    }
}