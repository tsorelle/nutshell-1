<?php

namespace Nutshell\cms;

use Peanut\users\AccountManager;
use Tops\sys\TUser;

class RouteFinder
{
    public static $matched = null;
    public static $routes = null;

    public static function match($uri)
    {
        if ($uri === '' || $uri === '/') {
            $uri = 'home';
        }
        self::$routes = parse_ini_file(DIR_CONFIG_SITE . '/routing.ini', true);
        foreach (self::$routes as $matchPath => $values) {
            if (strpos($uri, $matchPath) === 0) {
                if ($uri != $matchPath && (!array_key_exists('args',$values))) {
                    continue;
                }
                $matchParts = explode('/', $matchPath);
                $matchCount = count($matchParts);
                $pathParts = explode('/', $uri);
                for ($i = 0; $i < $matchCount; $i++) {
                    if ($pathParts[$i] !== $matchParts[$i]) {
                        return false;
                    }
                }
                $configuration = $values;
                $pathCount = count($pathParts);
                $argCount = $pathCount - $matchCount;
                if ($argCount > 0) {
                    $argValues = array_splice($pathParts, $matchCount);
                }

                $configuration['uri'] = $uri;
                $configuration['path'] = $matchPath;
                $configuration['argValues'] = $argValues ?? [];
                self::$matched = $configuration;

                return true;
            }
        }
        return false;
    }
}