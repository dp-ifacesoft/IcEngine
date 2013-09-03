<?php

/**
 * Стандартный Dto, по сути менеджер
 *
 * @author neon
 * @Service("dto")
 */
class Dto
{
    /**
     * Параметры по умолчанию
     *
     * @var array
     */
    protected static $defaults = array(
        'sortDir'   => 'Desc'
    );

    /**
     * Данные
     *
     * @var array
     */
    protected $fields = array();

    /**
     * Схема
     *
     * @var array
     */
    protected static $scheme = array();

    /**
     * (non-PHPDoc)
     */
    public function __construct()
    {
        $this->fields = static::$defaults;
    }

    /**
     * @see StdClass::__call
     *
     * @param type $method
     * @param type $args
     */
    public function __call($method, $args)
    {
        $regExp = '#([a-z]+)([A-Za-z]+)#';
        $matches = array();
        preg_match($regExp, $method, $matches);
        if (empty($matches[2])) {
            return;
        }
        $value = $matches[1] != 'disable';
        $arg = func_get_arg(1);
        if ($arg) {
            $argReseted = reset($arg);
            $value = $argReseted;
        }
        $key = lcfirst($matches[2]);
        $this->fields[$key] = $value;
        return $this;
    }

    /**
     * @see StdClass::__get
     *
     * @param type $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        }
        return null;
    }

    /**
     * @see StdClass::__set
     *
     * @param type $key
     * @param type $value
     */
    public function __set($key, $value)
    {
        $this->fields[$key] = $value;
    }

    /**
     * Получить значение поля
     *
     * @param string $key
     * @return type
     */
    public function getField($key)
    {
        return isset($this->fields[$key]) ? $this->fields[$key] : null;
    }

    /**
     * Получить данные Dto
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Создаёт экземпляр класса
     *
     * @return Dto
     */
    public function newInstance($name = null)
    {
        if ($name) {
            $className = $name . '_Dto';
            return new $className();
        }
        return new static();
    }

    /**
     * Установить значения массива
     *
     * @return Dto
     */
    public function set()
    {
        $argsCount = func_num_args();
        if ($argsCount == 1) {
            $this->setArray(func_get_arg(0));
        } elseif ($argsCount == 2) {
            list($key, $value) = func_get_args();
            $this->fields[$key] = $value;
        }
        return $this;
    }

    /**
     * Изменить данные
     *
     * @param array $array
     * @return Dto
     */
    public function setArray($array)
    {
        foreach ($array as $key => $value) {
            if (!empty(static::$scheme) && !in_array($key, static::$scheme)) {
                continue;
            }
            $this->fields[$key] = $value;
        }
    }
}