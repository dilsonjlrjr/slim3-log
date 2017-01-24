<?php
/**
 * Created by PhpStorm.
 * User: dilsonrabelo
 * Date: 30/12/16
 * Time: 19:49
 */

namespace Slim3\Log;

use Interop\Container\ContainerInterface;
use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LogMiddleware
{
    private $arrayLevelMonolog = [
        'DEBUG' => Logger::DEBUG,
        'INFO' => Logger::INFO,
        'NOTICE' => Logger::NOTICE,
        'WARNING' => Logger::WARNING,
        'ERROR' => Logger::ERROR,
        'CRITICAL' => Logger::CRITICAL,
        'ALERT' => Logger::ALERT,
        'EMERGENCY' => Logger::EMERGENCY
    ];

    private function castArray(array $arrayElement) {
        $stringReturn = "";

        foreach ($arrayElement as $key => $elem) {
            $stringReturn .= $key . '=' . $elem . ',';
        }

        return substr($stringReturn, 0, strlen($stringReturn) - 1);
    }


    /**
     * @param $request
     * @param $response
     * @param $next
     * @return mixed
     * @throws \Exception
     */
    function __invoke(RequestInterface $request, ResponseInterface $response, $next)
    {
        $route = $request->getAttribute('route');
        $arrayInfo = explode(':', $route->getCallable());

        $classReflection = new \ReflectionClass($arrayInfo[0]);

        $arrayLog = [];
        foreach ($classReflection->getMethods() as $method) {
            if ($method->getName() === $arrayInfo[1]) {
                preg_match('/@Log\s*\(([^)]+)\)/', $method->getDocComment(), $arrayLog);
                break 1;
            }
        }

        if (count($arrayLog)) {
            //Type
            preg_match('/type\s{0,}=\s{0,}["\']([^\'"]*)["\']/', $arrayLog[1], $arrayType);

            //Persist
            preg_match('/persist\s{0,}=\s{0,}\{(.*?)\}/', $arrayLog[1], $arrayPersist);

            //Message
            preg_match('/message\s{0,}=\s{0,}["\']([^\'"]*)["\']/', $arrayLog[1], $arrayMessage);

            if (!(count($arrayType) && count($arrayPersist) && count($arrayMessage))) {
                throw new \Exception('Annotation bad formated. Look controller ' . $route->getCallable() .
                    '. Example: @Log(type="INFO", persist={"verb", "session", "attributes"}, message="User authenticate in system")');
            }

            $type = $arrayType[1];
            $message = $arrayMessage[1];

            if (!key_exists($type, $this->arrayLevelMonolog))
            {
                throw new \Exception('Annotation bad formated. Look controller ' . $route->getCallable() . '. Type LOG (INFO, CRITICAL, NOTICE...).');
            }

            $arrayPersist = explode(",", trim($arrayPersist[1]));
            $logPersist = "";

            foreach ($arrayPersist as $persist) {
                switch (strtolower(str_replace('"', "",$persist))) {
                    case "verb":
                        $logPersist .= $request->getMethod() . '#';
                        break;
                    case "attributes":
                        $logPersist .= $this->castArray($request->getParams()) . '#';
                        break;
                }
            }

            $logger = SlimMagicMonologFacade::getLogger();
            $logger->addRecord($this->arrayLevelMonolog[$type], $message . ': ' . $logPersist);

        }
        return $next($request, $response);
    }


}