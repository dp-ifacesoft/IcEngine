<?php

/**
 * Источник услуг
 *
 * @author morph
 */
class Service_Source
{
    /**
     * Менеджер аннотаций
     *
     * @var Annotation_Manager_Abstract
     */
    protected $annotationManager;

    /**
     * Локатор сервисов источника
     *
     * @var Service_Locator
     */
    protected $locator;

    /**
     * Сервисы
     *
     * @var array
     */
    protected static $services;

    /**
     * Добавить сервис в источник
     *
     * @param string $serviceName
     * @param array $serviceData
     */
    public function addService($serviceName, $serviceData)
    {
        self::$services[$serviceName] = $serviceData;
    }

    /**
     * Построить сервис
     *
     * @param string $serviceName
     * @param array $serviceData
     * @return mixed
     */
    protected function buildService($serviceName, $serviceData)
    {
        $className = $serviceData['class'];
        if (!$className && empty($serviceData['source'])) {
            return;
        }
        $object = null;
        if (!empty($serviceData['source']) || !empty($serviceData['args'])) {
            $args = !empty($serviceData['args']) ? $serviceData['args'] :
                array();
            if ($args) {
                foreach ($args as &$arg) {
                    $arg = $this->getArg($arg);
                }
            }
            if (empty($serviceData['source'])) {
                $reflection = new \ReflectionClass($className);
                $object = $reflection->newInstanceArgs($args);
            } else {
                if (!empty($serviceData['source'])) {
                    $sourceData = $serviceData['source'];
                    if (!empty($sourceData['name'])) {
                        $source = $this->getService($sourceData['name']);
                        $this->locator->registerService(
                            $sourceData['name'], $source
                        );
                    } else {
                        if (empty($sourceData['isAbstract'])) {
                            $source = new $className;
                        } else {
                            $source = $className;
                        }
                    }
                }
                $method = $sourceData['method'];
                if (empty($serviceData['isStatic'])) {
                    self::$services[$serviceName]['instanceCallback'] = array(
                        array($source, $method), $args
                    );
                }
                $object = call_user_func_array(array($source, $method), $args);
                $className = get_class($object);
                self::$services[$serviceName]['class'] = $className;
            }
        } else {
            if (!empty($serviceData['disableConstruct'])) {
                $reflection = new \ReflectionClass($className);
                $object = $reflection->newInstanceWithoutConstructor();
                self::$services[$serviceName]['isAbstract'] = true;
            } else {
                if ($className == 'Social_User_Dialog') {
                    print_r('asdf');die;
                }
                $object = new $className;
            }
        }
        if ($this->annotationManager) {
            $realObject = $object;
            if ($object instanceof Service_State) {
                $realObject = $object->__object();
            }
            $annotations = $this->annotationManager->getAnnotation($realObject)
                ->getData();
            if (!empty($annotations['properties'])) {
                $properties = $annotations['properties'];
                $reflection = null;
                foreach ($properties as $propertyName => $data) {
                    if (!isset($data['Inject']) && !isset($data['Service'])) {
                        continue;
                    }
                    if (isset($data['Inject'])) {
                        $values = array_values($data['Inject'][0]);
                        $serviceName = $values[0];
                        $service = $this->locator->getService($serviceName);
                    } elseif (isset($data['Service'])) {
                        $serviceName = reset($data['Service'][0]);
                        $this->addService($serviceName, $data['Service'][0]);
                        $service = $this->locator->getService($serviceName);
                    }
                    $methodName = 'set' . ucfirst($propertyName);
                    if (method_exists($realObject, $methodName)) {
                        $realObject->$methodName($service);
                    } else {
                        if (!$reflection) {
                            $reflection = new \ReflectionClass($className);
                        }
                        $propertyReflection = $reflection->getProperty(
                            $propertyName
                        );
                        $propertyReflection->setAccessible(true);
                        if ($propertyReflection->isStatic()) {
                            $reflection->setStaticPropertyValue(
                                $propertyName, $service
                            );
                        } else {
                            $propertyReflection->setValue(
                                $realObject, $service
                            );
                        }
                    }
                }
            }
        }
        return $object;
    }

    /**
     * Получить менеджер аннотаций
     *
     * @return Annotation_Manager_Abstract
     */
    public function getAnnotationManager()
    {
        return $this->annotationManager;
    }

    /**
     * Получить аргумент
     *
     * @param string $arg
     */
    protected function getArg($arg)
    {
        if ($arg && $arg[0] == '$') {
            $arg = $this->locator->getService(substr($arg, 1));
        }
        return $arg;
    }

    /**
     * Получить локатор сервисов
     *
     * @return Service_Locator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        $service = null;
        if(is_null(self::$services)) {
            $this->loadServices();
        }
        if (!isset(self::$services[$serviceName])) {
            return null;
        }
        $serviceData = &self::$services[$serviceName];
        if (!isset($serviceData['class']) && !isset($serviceData['source'])) {
            return null;
        }
        if (!isset($serviceData['class'])) {
            $serviceData['class'] = null;
        }
        if (empty($serviceData['isAbstract'])) {
            $service = $this->buildService($serviceName, $serviceData);
            if (!$service) {
                return null;
            }
        }
        $instanceCallback = array();
        if (!empty(self::$services[$serviceName]['instanceCallback'])) {
           $instanceCallback = self::$services[$serviceName]['instanceCallback'];
        }
        if ($instanceCallback || !empty($serviceData['isAbstract'])) {
            $state = new Service_State(
                $service,
                self::$services[$serviceName]['class'],
                $instanceCallback
            );
        } else {
            $state = $service;
        }
        return $state;
    }

    /**
     * Загрузить конфигурацию сервисов
     */
    protected function loadServices()
    {
        $filename = IcEngine::root() . 'Ice/Config/Service/Source.php';
        if (is_file($filename)) {
            self::$services = include_once($filename);
        } else {
            self::$services = array();
        }
    }

    /**
     * Менеджер аннотаций
     *
     * @param Annotation_Manager_Abstract $annotationManager
     */
    public function setAnnotationManager($annotationManager)
    {
        $this->annotationManager = $annotationManager;
    }

    /**
     * Изменить локатор сервисов
     *
     * @param Service_Locator $locator
     */
    public function setLocator($locator)
    {
        $this->locator = $locator;
    }
}