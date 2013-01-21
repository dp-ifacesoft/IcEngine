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
     * @var array
     */
    protected static $defaults = array();

    /**
     * Данные
     * @var array
     */
    protected $fields = array();

    /**
     * Схема
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
        $key = lcfirst($matches[2]);
        $this->fields[$key] = $value;
        return $this;
    }

    /**
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
     *
     * @param type $key
     * @param type $value
     */
    public function __set($key, $value)
    {
        $this->fields[$key] = $value;
    }

    /**
     * Создаёт экземпляр класса
     * @return Dto
     */
    public function newInstance($name)
    {
        if ($name) {
            $className = $name . '_Dto';
            return new $className();
        }
        return new self();
    }

    /**
     * Установить значения массива
     * @param array $array
     * @return Dto
     */
    public function set($array)
    {
        if (empty(static::$scheme)) {
            return $this;
        }
        foreach ($array as $key => $value) {
            if (!in_array($key, static::$scheme)) {
                continue;
            }
            $this->fields[$key] = $value;
        }
        return $this;
    }
}