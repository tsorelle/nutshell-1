<?php
namespace Nutshell\cms;
use Peanut\sys\ViewModelManager;
use Tops\sys\TSession;

class Router
{
    public static function Execute() {
        switch (RouteFinder::$matched['handler'] ?? null) {
            case 'page' :
                self::routePage();
                break;
            case 'service' :
                self::routeService();
                break;
            default:
                throw new \Exception('Invalid configuation, must include "handler"');
        }
        return true;
    }

    public static function routeService()
    {
        include __DIR__.'\routing\ServiceRequestHandler.php';
        $routeData = RouteFinder::$matched;
        $method = $routeData['method'] ?? null;
        if (empty($method)) {
            throw new \Exception('Value "method" is required in service routing configuration.');
        }
        $handler = new ServiceRequestHandler();
        $argValues = $routeData->argValues ?? [];
        if (!empty($argValues)) {
            $handler->$method(...$argValues);
        }
        else {
            $handler->$method();
        }
        exit;
    }

    private static function routePage()
    {

        /*
          Additional configuration values
                openpanel
                paneltitle
                addwrapper
                inputvalue
        */

        $routeData = RouteFinder::$matched;
        $uri = $routeData['uri'];
        $theme = $routeData['theme'] ?? 'default';
        $routeData['theme'] = $theme;
        $routeData['themePath'] = '/application/themes/' . $theme;
        $routeData['themeIncludePath'] = DIR_BASE."/application/themes/$theme/inc";

        if ($theme === 'plain') {
            $routeData['maincolsize'] = 12;
        }
        else {
            $showSiteHeader= $routeData['header'] ?? 1;
            if ($showSiteHeader !== 1) {
                unset($routeData['showSiteHeader']);
            }
            $showSiteFooter= $routeData['footer'] ?? 1;
            if ($showSiteFooter !== 1) {
                unset($routeData['footer']);
            }
            $showBreadCrumbs = $routeData['breadcrumbs'] ?? 1;
            if ($showBreadCrumbs !== 1) {
                unset($routeData['breadcrumbs']);
            }
            $maincolsize = 12;
            if (isset($routeData['menu'])) {
                if (!isset($routeData['colsize'])) {
                    $routeData['colsize'] = 4;
                }
                $maincolsize -= $routeData['colsize'];
                if (!isset($routeData['menutype'])) {
                    $routeData['menutype'] = 'default';
                }
            }

            $routeData['maincolsize'] = $maincolsize;

        }

        if (isset($routeData['view'])) {
            $view = DIR_APPLICATION . '/content/pages/' . $routeData['view'] . '.php';
        } else if (isset($routeData['mvvm'])) {
            $viewModelKey = $routeData['mvvm'];
            $vmInfo = ViewModelManager::getViewModelSettings($viewModelKey);

            if (empty($vmInfo)) {
                $errorMessage = "Error: Cannot find view model configuration for '$viewModelKey'</h2>";
            } else {
                $viewResult = $vmInfo->view ?? null;
                if ($viewResult == 'content') {
                    $errorMessage = 'Embedded views not supported in Nutshell';
                } else {
                    $view = DIR_BASE . '/' . $viewResult;
                    if (!file_exists($view)) {
                        $errorMessage = "View file not found: $viewResult";
                    }
                }

                if (!isset($errorMessage)) {
                    $argNames = $argNames = $routeData['args'] ?? '';
                    if ($argNames) {
                        $argNames = explode(',',$argNames);
                        $argValues = $routeData['argValues'] ?? [];
                        if (!empty($routeData['argValues'])) {
                            $valueCount = count($argValues);
                            while(count($argNames) > $argValues) {
                                array_shift($argNames);
                            }

                            $pageVars = [];
                            for ($i = 0;$i < $valueCount; $i++) {
                                $pageVars[$argNames[$i]] = $argValues[$i];
                            }
                            $routeData['pageVars'] = $pageVars;
                            unset($routeData['args']);
                        }
                    }

                    $array = explode('/', $vmInfo->vmName);
                    $containerId = array_pop($array);
                    $routeData['containerId'] = strtolower($containerId) . "-view-container";

                    // init security token
                    TSession::Initialize();
                }

            }
            if (isset($errorMessage)) {
                $view = 'error-page.php';
                $routeData['errorMessage'] = $errorMessage;
                unset($routeData['mvvm']);
                unset($routeData['viewcontainerid']);
                unset($routeData['inputvalue']);
                unset($routeData['paneltitle']);
                unset($routeData['openpanel']);
                unset($routeData['addwrapper']);
            }
        }

        $routeData['view'] = $view;
        $routeData['sitemap'] = new SiteMap($uri);
        extract($routeData);
        include DIR_APPLICATION . '/content/page.php';
    }
    
}