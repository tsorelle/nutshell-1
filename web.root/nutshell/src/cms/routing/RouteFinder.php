<?php

namespace Nutshell\cms;

class RouteFinder
{
    public static $matched = null;
    public static  function match ($uri)
    {
        $path = '/';
        $argValues = [];
        $configuration = [];

        if ($uri === '') {
            $uri = 'home';
        }
        else {
            $config = parse_ini_file(DIR_CONFIG_SITE.'/routing.ini',true);
            foreach ($config as $matchPath => $values) {
                if (strpos($uri, $matchPath) === 0) {
                    if ($uri !== 'home') {
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
                    }

                    $configuration['uri'] = $uri;
                    $configuration['path'] = $matchPath;
                    $configuration['argValues'] = $argValues;
                    self::$matched = $configuration;

                    return true;
                }
            }



        }

        return false;
    }
}