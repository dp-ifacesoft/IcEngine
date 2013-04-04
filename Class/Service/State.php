<?php

/**
 * Состояние сервиса
 *
 * @author morph
 */
class Service_State
{
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
    public function __construct($object, $className, $instanceCallback)
    {
        $this->object = $object;
        $this->className = $className;
        $this->instanceCallback = $instanceCallback;
    }

    /**
     * Вызвать метод
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, $args)
    {
        if (is_bool($this->instanceCallback)) {
            if (!$this->classReflection) {
                $this->classReflection = new \ReflectionClass($this->className);
            }
            $methodReflection = $this->classReflection->getMethod($method);
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
            return $methodReflection->invokeArgs($this->object, $args);
        }
        return $this->classReflection->newInstanceArgs($args);
    }
}