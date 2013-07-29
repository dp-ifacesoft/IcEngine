<?php

/**
 * Состояние сервиса
 *
 * @author morph
 */
class Service_State
{
    /**
     * Аннотации
     * 
     * @var array
     */
    protected $annotations;
    
    /**
     * Имя класса услуги
     *
     * @var string
     */
    protected $className;

    /**
     * Рефлексия класса сервиса
     *
     * @var \ReflectionClass
     */
    protected $classReflection;
    
    /**
     * Внедренния в оригинальный объект
     * 
     * @var array
     */
    protected $injects = array();
    
    /**
     * Обработчик нового вызова сервиса
     *
     * @var array
     */
    protected $instanceCallback;

    /**
     * Экземпляр объекта
     *
     * @var mixed
     */
    protected $object;

    /**
     * Конструктор
     *
     * @param mixed $object
     */
    public function __construct($object, $className, $instanceCallback,
        $injects)
    {
        $this->object = $object;
        $this->className = $className;
        $this->instanceCallback = $instanceCallback;
        $this->injects = $injects;
        $serviceLocator = IcEngine::serviceLocator();
        $this->annotations = $serviceLocator->getService('helperAnnotation')
            ->getAnnotation($className)->getData();
    }

    /**
     * Вызвать метод
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        $serviceLocator = IcEngine::serviceLocator();
        if (isset($this->annotations['methods'][$method]['Inject'])) {
            $injectData = $this->annotations['methods'][$method]['Inject'];
            foreach (reset($injectData) as $i => $serviceName) {
                $args[] = $serviceLocator->getService($serviceName);
            }
        } elseif (isset($this->annotations['class']['Injectible'])) {
            $methodReflection = $this->getMethodReflection($method);
            $methodArgs = $methodReflection->getParameters();
            $serviceNames = array_slice($methodArgs, count($args));
            foreach ($serviceNames as $serviceName) {
                $args[] = $serviceLocator->getService($serviceName->name);
            }
        }
        if (is_bool($this->instanceCallback)) {
            $methodReflection = $this->getMethodReflection($method);
            if ($methodReflection->isStatic()) {
                return call_user_func_array(
                    array($this->className, $method), $args
                );
            }
        } elseif (is_array($this->instanceCallback) && 
            $this->instanceCallback) {
            $result = call_user_func_array(
                $this->instanceCallback[0], $this->instanceCallback[1]
            );
            if ($this->object) {
                $this->object = $result;
            }
        }
        if ($this->object) {
            return call_user_func_array(
                array($this->object, $method), $args
            );
        }
        return call_user_func_array(array($this->className, $method), $args);
    }

    /**
     * Получить класс объекта
     *
     * @return string
     */
    public function __class()
    {
        return $this->className;
    }

    /**
     * Получить объект состояния
     *
     * @return mixed
     */
    public function __object()
    {
        return $this->object;
    }

    /**
     * Получить рефлексию метода
     * 
     * @param string $method
     * @return \ReflectionMethod
     */
    public function getMethodReflection($method)
    {
        if (!$this->classReflection) {
            $this->classReflection = new \ReflectionClass($this->className);
        }
        $methodReflection = $this->classReflection->getMethod($method);
        return $methodReflection;
    }
    
    /**
     * Создать новый экземпляр сервиса
     *
     * @return mixed
     */
    public function newInstance()
    {
        if (!$this->object) {
            return;
        }
        if (!$this->classReflection) {
            $this->classReflection = new \ReflectionClass(
                get_class($this->object)
            );
        }
        $args = func_get_args();
        if (method_exists($this->object, 'newInstance')) {
            $methodReflection = $this->classReflection->getMethod(
                'newInstance'
            );
            $instance = $methodReflection->invokeArgs($this->object, $args);
        } else {
            $instance = $this->classReflection->newInstanceArgs($args);
        }
        if ($this->injects) {
            foreach ($this->injects as $propertyName => $service) {
                $setterName = 'set' . ucfirst($propertyName);
                if (method_exists($instance, $setterName)) {
                    $instance->$setterName($service);
                } 
            }
        }
        return $instance;
    }
}