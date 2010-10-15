<?php
/**
 * MiniMVC_Dispatcher delegates requests and route/widget calls to the right controllers and actions
 */
class MiniMVC_Dispatcher
{
    /**
     *
     * @var MiniMVC_Registry
     */
    protected $registry = null;

    public function __construct()
    {
        $this->registry = MiniMVC_Registry::getInstance();
    }

    /**
     *
     * @return string returns the parsed output of the current request
     */
    public function dispatch()
    {
        $protocol = (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS'] || $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
		$host = $protocol.'://'.$_SERVER['HTTP_HOST'];
        $url = $host . $_SERVER['REQUEST_URI'];

        if (isset($_POST['REQUEST_METHOD'])) {
            $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['REQUEST_METHOD']);
        }
        $method = (!empty($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        $currentLanguage = null;
        $currentApp = $this->registry->settings->get('runtime/currentApp');
        $route = null;

        if (!$currentApp || !$this->registry->settings->get('apps/'.$currentApp)) {
            $defaultApp = $this->registry->settings->get('config/defaultApp');
            if (!$redirectUrl = $this->registry->settings->get('apps/'.$defaultApp.'/baseurl')) {
                throw new Exception('No matching App found and no default app configured!');
            }
            header('Location: ' . $redirectUrl);
            exit;
        }

        $appurls = $this->registry->settings->get('apps/'.$currentApp);
        if (isset($appurls['baseurlI18n'])) {
            if (preg_match('#' . str_replace(':lang:', '(?P<lang>[a-z]{2})', $appurls['baseurlI18n']) . '(?P<route>[^\?\#]*).*$#', $url, $matches)) {
                $currentLanguage = $matches['lang'];
                $route = $matches['route'];
            }
        }
        if ($route === null && isset($appurls['baseurl'])) {
            if (preg_match('#' . $appurls['baseurl'] . '(?P<route>[^\?\#]*).*$#', $url, $matches)) {
                $route = $matches['route'];
            } else {
                $route = false;
            }
        }

        if ($currentLanguage) {
            if (!in_array($currentLanguage, $this->registry->settings->get('config/enabledLanguages', array())) || $currentLanguage == $this->registry->settings->get('config/defaultLanguage')) {
                if (!$redirectUrl = $this->registry->settings->get('apps/'.$currentApp.'/baseurl')) {
                    throw new Exception('No baseurl for App '.$currentApp.' found!');
                }
                header('Location: ' . $redirectUrl . $route . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
                exit;
            }
        } else {
            if (!$currentLanguage = $this->registry->settings->get('config/defaultLanguage')) {
                throw new Exception('No default language for App '.$currentApp.' found!');
            }
        }
        $this->registry->settings->set('runtime/currentLanguage', $currentLanguage);

        $this->registry->settings->set('runtime/requestedRoute', $route);

        $routes = $this->registry->settings->get('routes');
        $routeData = null;

        if (!$route && $route !== false) {
            $defaultRoute = $this->registry->settings->get('config/defaultRoute');
            if ($defaultRoute && isset($routes[$defaultRoute])) {
                $routeName = $defaultRoute;
                $routeData = $routes[$defaultRoute];
            } else {
                throw new Exception('no route given and no default Route defined!');
            }
        } else {
            $found = false;

            if ($route) {
                foreach ($routes as $currentRoute => $currentRouteData) {
                    if (isset($currentRouteData['active']) && !$currentRouteData['active']) {
                        continue;
                    }
                    if (!isset($currentRouteData['route']) || !isset($currentRouteData['controller']) || !isset($currentRouteData['action'])) {
                        continue;
                    }
                    if (isset($currentRouteData['method']) && ((is_string($currentRouteData['method']) && $currentRouteData['method'] != $method) || (is_array($currentRouteData['method']) && !in_array($method, $currentRouteData['method'])))) {
                        continue;
                    }
                    $routePattern = (isset($currentRouteData['routePatternGenerated'])) ? $currentRouteData['routePatternGenerated'] : $this->getRegex($currentRoute, $currentRouteData);
                    if (preg_match($routePattern, $route, $matches)) {
                        $params = (isset($currentRouteData['parameter'])) ? $currentRouteData['parameter'] : array();
                        foreach ($matches as $paramKey => $paramValue) {
                            if (!is_numeric($paramKey)) {
                                if ($paramKey == 'anonymousParams') {

                                    foreach (explode('/', $paramValue) as $anonymousParam) {
                                        $anonymousParam = explode('-', $anonymousParam, 2);
                                        if (trim($anonymousParam[0]) && !isset($params[urldecode($anonymousParam[0])])) {
                                            $params[urldecode($anonymousParam[0])] = (isset($anonymousParam[1])) ? urldecode($anonymousParam[1]) : '';
                                        }
                                    }
                                } else {
                                    $params[urldecode($paramKey)] = urldecode($paramValue);
                                }
                            }
                        }

                        $routeName = $currentRoute;
                        //$routeData = $this->getRoute($currentRoute, $params);
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $error404Route = $this->registry->settings->get('config/error404Route');
                if ($error404Route && isset($routes[$error404Route])) {
                    $routeName = $error404Route;
                    $routeData = $routes[$error404Route];
                } else {
                    throw new Exception('no valid route found and no valid 404 Route defined!');
                }
            }
        }        

        
        
        try {

            $this->registry->settings->set('runtime/currentRoute', $routeName);
            $this->registry->settings->set('runtime/currentRouteParameter', isset($params) ? $params : array());

            $this->registry->events->notify(new sfEvent($this, 'minimvc.init'));

            $this->registry->db->init();
            
            $content = $this->callRoute($routeName, (isset($params) ? $params : array()), true, true);
            return $this->registry->template->prepare($content, $this->registry->settings->get('runtime/currentApp'));
        } catch (Exception $e) {
            $error500Route = $this->registry->settings->get('config/error500Route');
            if ($error500Route && isset($routes[$error500Route])) {
                $routeData = $routes[$error500Route];
                $routeData['parameter']['exception'] = $e;
                $content = $this->call($routeData['controller'], $routeData['action'], (isset($routeData['parameter']) ? $routeData['parameter'] : array()));
                return $this->registry->template->prepare($content, $this->registry->settings->get('runtime/currentApp'), false);
            } else {
                throw new Exception('Exception was thrown and no 500 Route defined!');
            }
        }
    }

    /**
     *
     * @param string $route the name of an internal route
     * @param array $params the parameters for the route
     * @param mixed $app the name of an app or null to use the current app
     * @return array returns an array with route information
     */
    public function getRoute($route, $params = array(), $app = null)
    {
        $app = ($app) ? $app : $this->registry->settings->get('runtime/currentApp');

        if (!$routeData = $this->registry->settings->get('routes/'.$route, array(), $app)) {
            throw new Exception('Route "' . $route . '" does not exist!');
        }
        
        $routeData['parameter'] = (isset($routeData['parameter'])) ? array_merge($routeData['parameter'], (array)$params) : (array)$params;

        $event = new sfEvent($this, 'minimvc.dispatcher.filterRoute');
        $this->registry->events->filter($event, $routeData);
        $routeData = $event->getReturnValue();

        return $routeData;
    }

    /**
     *
     * @param string $route the name of an internal route
     * @param array $params the parameters for the route
     * @param bool $showErrorPages if true, the 401/403 error pages will be called if the user has insuficcient rights
     * @return MiniMVC_View the prepared view class of the called action
     */
    public function callRoute($route, $params = array(), $showErrorPages = true, $isMainRoute = true)
    {
        $routeData = $this->getRoute($route, $params);

        if ($isMainRoute) {
            if (isset($routeData['format'])) {
                $this->registry->template->setFormat($routeData['format']);
            } elseif(isset($routeData['parameter']['_format'])) {
                $this->registry->template->setFormat($routeData['parameter']['_format']);
            }

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($routeData['parameter']['_controller']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                $this->registry->template->setLayout(false);
            } elseif (isset($routeData['layout'])) {
                $this->registry->template->setLayout($routeData['layout']);
            }

        }

        if (isset($routeData['parameter']['_action'])) {
            $routeData['action'] = $routeData['parameter']['_action'];
        } elseif(!isset($routeData['action'])) {
            $routeData['action'] = 'index';
        }

        if (isset($routeData['parameter']['_controller'])) {
            if (isset($routeData['parameter']['_module'])) {
                $routeData['controller'] = $routeData['parameter']['_module'] . '_' . $routeData['parameter']['_controller'];
            } else {
                $routeData['controller'] = 'My_' . $routeData['parameter']['_controller'];
            }
        } elseif(!isset($routeData['controller'])) {
            $routeData['controller'] = 'My_Default';
        }


        if (isset($routeData['rights']) && $routeData['rights'] && !((int)$routeData['rights'] & $this->registry->guard->getRights())) {
            if (!$showErrorPages) {
                return null;
            }
            if ($this->registry->guard->getRole() && $this->registry->guard->getRole() != $this->registry->rights->getRoleByKeyword('guest')) {
                $error403Route = $this->registry->settings->get('config/error403Route');
                if (!$error403Route || !$routeData = $this->registry->settings->get('routes/'.$error403Route)) {
                    throw new Exception('Insufficient rights and no 403 Route defined!');
                }
            } else {
                $error401Route = $this->registry->settings->get('config/error401Route');
                if (!$error401Route ||!$routeData = $this->registry->settings->get('routes/'.$error401Route)) {
                    throw new Exception('Not logged in and no 401 Route defined!');
                }
            }
        }

        if (isset($routeData['model']) && is_array($routeData['model'])) {
            if (isset($routeData['model'][0]) && !is_array($routeData['model'][0])) {
                $routeData['model'] = array($routeData['model']);
            }
            $models = array();
            foreach ($routeData['model'] as $modelKey => $modelData) {
                $modelName = $modelData[0];
                $tableName = $modelName.'Table';
                if (!class_exists($modelName) || !class_exists($tableName)) {
                    $models[$modelKey] = null;
                } else {
                    $table = new $tableName;
                    $property = !empty($modelData[1]) ? $modelData[1] : $table->getIdentifier();
                    $refProperty = !empty($modelData[2]) ? $modelData[2] : $property;
                    $models[$modelKey] = empty($routeData['parameter'][$refProperty]) ? null : $table->loadOneBy($property.' = ?', $routeData['parameter'][$refProperty]);
                }
            }
            $routeData['parameter']['model'] = (count($models) === 1 && isset($models[0])) ? reset($models) : $models;
        }

        return $this->call($routeData['controller'], $routeData['action'], isset($routeData['parameter']) ? $routeData['parameter'] : array());
    }

    /**
     *
     * @param string $widget the name of an internal widget
     * @param array $params the parameters for the widget
     * @param $app the name of an app or null to use the current app
     * @return array returns an array with widget information
     */
    public function getWidget($widget, $params = array(), $app = null)
    {
        $app = ($app) ? $app : $this->registry->settings->get('runtime/currentApp');

        if (!$widgetData = $this->registry->settings->get('widgets/'.$widget, array(), $app)) {
            throw new Exception('Widget "' . $widget . '" does not exist!');
        }
        
        $widgetData['parameter'] = (isset($widgetData['parameter'])) ? array_merge($widgetData['parameter'], (array)$params) : (array)$params;
        return $widgetData;
    }

    /**
     *
     * @param string $widget the name of an internal widget
     * @param array $params the parameters for the widget
     * @return MiniMVC_View the prepared view class of the called widget
     */
    public function callWidget($widget, $params = array())
    {
        $widgetData = $this->getWidget($widget, $params);

        if (!isset($widgetData['controller']) || !isset($widgetData['action'])) {
            throw new Exception('Widget "' . $widget . '" is invalid (controller or action not set!');
        }

        if (isset($widgetData['rights']) && $widgetData['rights'] && !((int)$widgetData['rights'] & $this->registry->guard->getRights())) {
            return null;
        }
        
        $route = $this->registry->settings->get('runtime/currentRoute');
        $format = $this->registry->template->getFormat();
        $layout = $this->registry->template->getLayout();
        if (isset($widgetData['show']) && $widgetData['show']) {
            if (is_string($widgetData['show']) && $widgetData['show'] != $route) {
                return null;
            } elseif(is_array($widgetData['show']) && !in_array($route, $widgetData['show'])) {
                return null;
            }
        }
        if (isset($widgetData['hide']) && $widgetData['hide']) {
            if (is_string($widgetData['hide']) && $widgetData['hide'] == $route) {
                return null;
            } elseif(is_array($widgetData['hide']) && in_array($route, $widgetData['hide'])) {
                return null;
            }
        }
        if (!isset($widgetData['format']) || $widgetData['format'] == 'html') {
            $widgetData['format'] = null;
        }
        if (!is_array($widgetData['format']) && $widgetData['format'] != 'all' && ($widgetData['format'] != $format)) {
            return null;
        } elseif(is_array($widgetData['format']) && !in_array($format ? $format : 'html', $widgetData['format'])) {
            return null;
        }
        if (isset($widgetData['layout']) && $widgetData['layout']) {
            if (is_string($widgetData['layout']) && $widgetData['layout'] != 'all' && $widgetData['layout'] != $layout) {
                return null;
            } elseif(is_array($widgetData['layout']) && !in_array($layout, $widgetData['layout'])) {
                return null;
            }
        }

        return $this->call($widgetData['controller'], $widgetData['action'], $widgetData['parameter']);
    }

    /**
     *
     * @param string $task the name of an internal task
     * @param array $params the parameters for the task
     * @param $app the name of an app or null to use the current app
     * @return array returns an array with task information
     */
    public function getTask($task, $params = array(), $app = null)
    {
        $app = ($app) ? $app : $this->registry->settings->get('runtime/currentApp');
        if (!$taskData = $this->registry->settings->get('tasks/'.$task, array(), $app)) {
            throw new Exception('Task "' . $task . '" does not exist!');
        }

        if (isset($taskData['assign'])) {
            if (is_array($taskData['assign'])) {
                foreach ($taskData['assign'] as $k => $v) {
                    if (isset($params[$k])) {
                        $params[$v] = $params[$k];
                    }
                }
            } elseif (is_string($taskData['assign']) && isset($params[0])) {
                $params[$taskData['assign']] = $params[0];
            }
        }
        
        $taskData['parameter'] = (isset($taskData['parameter'])) ? array_merge($taskData['parameter'], $params) : $params;
        return $taskData;
    }

    /**
     *
     * @param string $task the name of an internal task
     * @param array $params the parameters for the task
     * @return MiniMVC_View the prepared view class of the called task
     */
    public function callTask($task, $params = array())
    {
        $taskData = $this->getTask($task, $params);

        if (!isset($taskData['controller']) || !isset($taskData['action'])) {
            throw new Exception('Task "' . $task . '" is invalid (controller or action not set!');
        }

        return $this->call($taskData['controller'], $taskData['action'], $taskData['parameter']);
    }

    /**
     *
     * @param string $controller the name of a controller
     * @param string $action the name of an action
     * @param array $params an array with parameters
     * @return MiniMVC_View the prepared view class of the called action
     */
    public function call($controller, $action, $params)
    {
        if (strpos($controller, '_') === false) {
            throw new Exception('Invalid controller "' . $controller . '"!');
        }
        $controllerParts = explode('_', $controller);
        $controllerName = $controllerParts[0] . '_' . $controllerParts[1] . '_Controller';
        $actionName = $action . 'Action';
        if (!class_exists($controllerName)) {
            throw new Exception('Controller "' . $controller . '" does not exist!');
        }
        if (!method_exists($controllerName, $actionName)) {
            throw new Exception('Action "' . $action . '" for Controller "' . $controller . '" does not exist!');
        }

        $viewName = $this->registry->settings->get('config/classes/view', 'MiniMVC_View');
        $view = new $viewName($controllerParts[0], strtolower($controllerParts[1].'/'.$action));

        $controllerClass = new $controllerName($view);
        $this->registry->events->notify(new sfEvent($controllerClass, 'minimvc.call', array('controller' => $controller, 'action' => $action, 'params' => $params)));
        $this->registry->events->notify(new sfEvent($controllerClass, strtolower($controllerParts[0]).'.'.strtolower($controllerParts[1]).'.'.strtolower($action).'.call', array('controller' => $controller, 'action' => $action, 'params' => $params)));
        $return = $controllerClass->$actionName($params);

        if(is_object($return) && $return instanceof $viewName) {
            return $return;
        } elseif ($return === false) {
            return $controllerClass->getView()->prepareEmpty();
        } elseif (is_string($return)) {
            return $controllerClass->getView()->prepareText($return);
        }

        return $controllerClass->getView();
    }

    /**
     *
     * @param string $route a name of an internal route
     * @param array $routeData information about the route
     * @return string returns a regular expression pattern to parse the called route
     */
    protected function getRegex($route, $routeData)
    {
        $routePattern = isset($routeData['routePattern']) ? $routeData['routePattern'] : str_replace(array('(',')','[',']','.','?'), array('\\(','\\)','\\[','\\]','\\.','\\?'), $routeData['route']);
        if (isset($routeData['parameterPatterns'])) {
            $search = array();
            $replace = array();
            foreach ($routeData['parameterPatterns'] as $param => $regex) {
                $search[] = ':' . $param . ':';
                $replace[] = '(?P<' . $param . '>' . $regex . ')';
            }

            $routePattern = str_replace($search, $replace, $routePattern);
        }
        $routePattern = preg_replace('#:([^:]+):#i', '(?P<$1>[^\./]+)', $routePattern);
        if (!empty($routeData['allowAnonymous'])) {
            if (substr($route, -1) == '/') {
                $routePattern .= '(?P<anonymousParams>([^-/]+-[^/]+/)*)';
            } else {
                $routePattern .= '(?P<anonymousParams>(/[^-/]+-[^/]+)*)';
            }
        }
        $routePattern = '#^' . $routePattern . '$#';

        $this->registry->settings->set('routes/'.$route.'/routePatternGenerated', $routePattern);

        return $routePattern;
    }

}