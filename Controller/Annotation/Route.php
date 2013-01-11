<?php

/**
 * Контроллер для аннотаций типа "Route"
 * 
 * @author morph
 */
class Controller_Annotation_Route extends Controller_Abstract
{
    /**
     * Распарсить аннотацию
     */
    public function update($data, $context) 
    {
        $routesWithoutGroups = array();
        $routesWithGroups = array();
        foreach ($data as $id => $data) {
            list($tmpName, $methodName) = explode('/', $id);
            $controllerName = substr($tmpName, strlen('Controller_'));
            if (!$data) {
                continue;
            }
            $hasAnnotation = false;
            foreach (array_keys($data) as $annotationName) {
                if (strpos($annotationName, 'Route') === 0) {
                    $hasAnnotation = true;
                    break;
                }
            }
            if (!$hasAnnotation) {
                continue;
            }
            if (empty($data['Route']['data'])) {
                continue;
            }
            foreach ($data['Route']['data'] as $i => $routeData) {
                $route = reset($routeData);
                $routeGroup = !empty($data['RouteGroup'])
                    ? resert($data['RouteGroup']['data']) : null;
                if (!$routeGroup && isset($routeData['group'])) {
                    $routeGroup = $routeData['group'];
                }
                $components = array();
                if (!empty($data['RouteComponent'])) {
                    foreach ($data['RouteComponent']['data'] as 
                        $routeComponent) {
                        $componentName = reset($routeComponent);
                        unset($routeComponent[$componentName]);
                        $components[$componentName] = $routeComponent;
                    }
                } elseif (!empty($routeData['components'])) {
                    $components = $routeData['components'];
                }
                $weight = !empty($data['RouteWeight'])
                    ? reset($data['RouteWeight']['data'][$i]) : 0;
                if (!$weight && !empty($routeData['weight'])) {
                    $weight = $routeData['weight'];
                }
                $params = array();
                if (!empty($data['RouteParam'])) {
                    foreach ($data['RouteParam']['data'] as $routeParam) {
                        $paramName = reset($routeParam);
                        $paramValue = isset($routeParam['value'])
                            ? $routeParam['value'] : null;
                        $params[$paramName] = $paramValue;
                    }
                } elseif (!empty($routeData['params'])) {
                    $params = $routeData['params'];
                }
                $actions = array($controllerName . '/' . $methodName);
                if (!empty($data['RouteAction'])) {
                    foreach ($data['RouteAction']['data'] as $routeAction) {
                        $actions[] = reset($routeAction);
                    }
                } elseif (!empty($routeData['actions'])) {
                    $actions = array_values($routeData['actions']);
                }
                $routeName = !empty($data['RouteName'])
                    ? reset($data['RouteName']['data'][$i]) : null;
                if (!$routeName && !empty($routeData['name'])) {
                    $routeName = $routeData['name'];
                }
                $theRoute = array(
                    'route'     => $route,
                    'weight'    => $weight, 
                    'actions'   => $actions
                );
                $routeIds[] = $route;
                if ($params) {
                    $theRoute['params'] = $params;
                }
                if ($components) {
                    $theRoute['patterns'] = $components;
                }
                $source = &$routesWithoutGroups;
                if ($routeGroup) {
                    if (!isset($routesWithGroups[$routeGroup])) {
                        $routesWithGroups[$routeGroup] = array();
                    }
                    $source = &$routesWithGroups[$routeGroup];
                }
                if ($routeName) {
                    $source[$routeName] = $theRoute;
                } else {
                    $source[] = $theRoute;
                }
            }
        }
        $config = $context->configManager->get('Route')->__toArray();
        $emptyRoute = isset($config['empty_route']) 
            ? $config['empty_route'] : array();
        if (!empty($config['routes'])) {
            foreach ($config['routes'] as $routeName => $route) {
                if (empty($route['route'])) {
                    continue;
                }
                if (in_array($route['route'], $routeIds)) {
                    continue;
                }
                $source = &$routesWithoutGroups;
                if (!empty($route['group'])) {
                    $routeGroup = $route['group'];
                    if (!isset($routesWithGroups[$routeGroup])) {
                        $routesWithGroups[$routeGroup] = array();
                    }
                    $source = &$routesWithGroups[$routeGroup];
                }
                if (is_numeric($routeName)) {
                    $source[] = $route;
                } else {
                    $source[$routeName] = $route;
                }
            }
        }
        ksort($routesWithGroups);
        ksort($routesWithoutGroups);
        $routes = array();
        if ($routesWithoutGroups) {
            foreach ($routesWithGroups as $groupRoutes) {
                foreach ($groupRoutes as $routeName => $route) {
                    if (is_numeric($routeName)) {
                        $routes[] = $route;
                    } else {
                        $routes[$routeName] = $route;
                    }
                }
            }
        }
        if ($routesWithoutGroups) {
            foreach ($routesWithoutGroups as $routeName => $route) {
                if (is_numeric($routeName)) {
                    $routes[] = $route;
                } else {
                    $routes[$routeName] = $route;
                }
            }
        }
        $output = Helper_Code_Generator::fromTemplate(
            'route',
            array (
                'routes'        => $routes,
                'empty_route'   => $emptyRoute
            )
        );
        $result = array();
        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) { 
            $baseLine = $line;
            $line = str_replace(array("\n", "\r"), '', trim($line));
            if (!$line) {
                continue;
            }
            $result[] = $baseLine;
        }
        $filename = IcEngine::root() . 'Ice/Config/Route.php';
        file_put_contents($filename, implode(PHP_EOL, $result));
    }
}