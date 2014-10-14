<?php

/**
 * Контроллер синхронизации всякого
 *
 * @author neon
 * @Service("sync")
 */
class Controller_Sync extends Controller_Abstract
{
    /**
     * ./ice Sync --name Agency --method metro --agencyIds 7353 --verbose
     * 
     * @Template(null)
     */
    public function index($name, $method)
    {
        if (!$name) {
            echo 'Name option is required: example --name Resort' . PHP_EOL;
            return;
        }
        $collectionManager = $this->getService('collectionManager');
        $syncCollection = $collectionManager
            ->create($name . '_Sync');
        if ($method) {
            $syncCollection->addOptions(array(
                'name'  => '::Name',
                'value' => $method
            ));
        }
        if (!$syncCollection) {
            echo 'Empty sync' . PHP_EOL;
            return;
        }
        $controllerManager = $this->getService('controllerManager');
        foreach ($syncCollection as $sync) {
            echo '----' . $sync['name'] . "\n";
            list($className, $methodName) = explode('/', $sync['method']);
            $isController = strpos($className, 'Controller_') !== false;
            if ($isController) {
                $controllerManager->call(
                    $className, $methodName, $this->input, null
                );
                continue;
            }
            $reflection = new ReflectionClass($className);
            $method = $reflection->getMethod($methodName);
            $params = $method->getParameters();
            $paramsPrepared = array();
            foreach ($params as $param) {
                $value = $this->input->receive($param->name);
                if (!$value && $param->isOptional()) {
                    $value = $param->getDefaultValue();
                }
                $paramsPrepared[$param->name] = $value;
            }
            $class = new $className();
            $method->invokeArgs($class, $paramsPrepared);
        }
    }
}