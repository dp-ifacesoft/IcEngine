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
     * Хелпер сервисов
     *
     * @Generator
     * @var Helper_String
     */
    protected $helper;

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
                $object = new $className;
            }
        }
        if ($this->annotationManager) {
            $this->processInjections($object, $serviceName, $className);
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
     * @return string
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
            $className = $this->helper()->normalizeName($serviceName);
//            if (!class_exists($className)) {
//                return null;
//            }
            $classReflection = new \ReflectionClass($className);
            self::$services[$serviceName] = array(
                'class'             => $className,
                'isAbstract'        => $classReflection->isAbstract(),
                'instanceCallback'  => false
            );
        }
        if (!isset(self::$services[$serviceName]['injects'])) {
            self::$services[$serviceName]['injects'] = array();
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
           $instanceCallback =
               self::$services[$serviceName]['instanceCallback'];
        }
        if ($instanceCallback || !empty($serviceData['isAbstract'])) {
            $state = new Service_State(
                $service,
                self::$services[$serviceName]['class'],
                $instanceCallback,
                self::$services[$serviceName]['injects']
            );
        } else {
            $state = $service;
        }
        return $state;
    }

    /**
     * Получить/создать хелпер для работы с сервисами
     *
     * @return Helper_Service
     */
    protected function helper()
    {
        if (!$this->helper) {
            $this->helper = new Helper_Service();
        }
        return $this->helper;
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
     * Внедрить зависимости
     * 
     * @param Service_State $object
     * @param string $serviceName
     * @param string $className
     */
    public function processInjections($object, $serviceName, $className)
    {
        $realObject = $object;
        if ($object instanceof Service_State) {
            $realObject = $object->__object();
        }
        $oldServiceName = $serviceName;
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
                    if (is_array($data['Inject'])) {
                        $values = array_values($data['Inject'][0]);
                        $serviceName = $values[0];
                    } else {
                        $serviceName = $propertyName;
                    }
                    $service = $this->locator->getService($serviceName);
                } elseif (isset($data['Service'])) {
                    $serviceName = reset($data['Service'][0]);
                    $this->addService($serviceName, $data['Service'][0]);
                    $service = $this->locator->getService($serviceName);
                }
                self::$services[$oldServiceName]['injects'][$propertyName] =
                    $service;
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
                        $propertyReflection->setValue($realObject, $service);
                    }
                }
            }
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

    /**
     * Getter for "helper"
     *
     * @return Helper_String
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Setter for "helper"
     *
     * @param Helper_String helper
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;
    }

}