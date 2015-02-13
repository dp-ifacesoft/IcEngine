<?php

/**
 * Обработчик аннотации, импортирующей роуты из внешнего конфига
 * 
 * @author morph
 */
class Controller_Annotation_RouteImport extends Controller_Abstract
{
    /**
     * Распарсить аннотацию
     * 
     * @Context("helperCodeGenerator")
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context) 
    {
        $routeConfig = $context->configManager->get('Route');
        $routes = $routeConfig->routes->__toArray();
        $emptyRoute = $routeConfig->emptyRoute;
        foreach ($data as $id => $currentData) {
            list($className,) = explode('/', $id, 2);
            if (empty($currentData['RouteImport']['data'])) {
                continue;
            }
            $configKey = reset($currentData['RouteImport']['data'][0]);
            if (strpos($configKey, '.') !== false) {
                list($className, $configKey) = explode('.', $configKey);
            }
            $classConfig = $context->configManager->get($className);
            if (!$classConfig) {
                continue;
            }
            $configRoutes = $classConfig[$configKey];
            if (!$configRoutes) {
                continue;
            }
            foreach ($classConfig[$configKey]->__toArray() as $name => $route) {
                $route['actions'] = array($id);
                if (is_numeric($name)) {
                    continue;
                } else {
                    $routes[$name] = $route;
                }
            }
        }
        $output = $context->helperCodeGenerator->fromTemplate(
            'route',
            array (
                'routes'        => $routes,
                'emptyRoute'    => $emptyRoute 
                    ? $emptyRoute->__toArray() : array()
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