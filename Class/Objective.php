<?php

/**
 * Объект с динамически создаваемыми полями. Может быть использован как массив.
 *
 * @author morph
 */
class Objective implements ArrayAccess, IteratorAggregate, Countable
{
	/**
	 * Данные объекта.
	 *
     * @var array
	 */
	protected $data = array();

	/**
	 * Возвращает экземпляр Objective
	 *
     * @param array $data
	 */
	public function __construct($data = array())
	{
        if (!$data || !is_array($data)) {
            return;
        }
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}
	}

	/**
	 * Клонирование (выполняется рекурсивно)
	 */
	public function __clone()
	{
		$data = array();
		foreach ($this->data as $key => $value) {
			if ($value instanceof Objective) {
				$data[$key] = clone $value;
			} else {
				$data[$key] = $value;
			}
		}
		$this->data = $data;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return isset($this->data[$key]) ? $this->data[$key] : null;
	}

	/**
	 * @desc Проверяет существование поля.
     *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->data[$key]);
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		if (is_array($value)) {
            $this->data[$key] = new self($value);
		} else {
			$this->data[$key] = $value;
		}
	}

	/**
	 * Данные объекта в виде массива. Данные типа Objective рекурсивно будут
     * приведены к массивам.
	 *
     * @return array
	 */
	public function __toArray()
	{
		$data = array();
		foreach ($this->data as $key => $value) {
			if ($value instanceof Objective) {
				$data[$key] = $value->__toArray();
			} else {
				$data[$key] = $value;
			}
		}
		return $data;
	}

	/**
	 * Данные объекта как массив. Если существуют данные типа Objective,
     * они будут переданны как объект без приведения к массиву
     * (в отличие от __toArray)
	 *
     * @return array
	 */
	public function asArray()
	{
		return $this->data;
	}

	/**
	 * Получить колонку объекта
	 *
     * @param string $column
	 * @return array<string>
	 */
	public function column($column)
	{
		$result = array();
		foreach ($this as $item) {
			$result[] = ($item instanceof Objective) ? $item->$column : null;
		}
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see Countable::count()
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Проверка на существование поля.
     *
	 * @param string $key
	 * @return boolean
	 */
	public function exists($key)
	{
		return isset($this->data[$key]);
	}

	/**
     * Получить часть данных по ключу
     *
	 * @param string $path
	 * @return mixed
	 */
	public function get()
	{
		$result = $this->__toArray();
        $path = func_get_arg(0);
        if (strpos($path, '/')) {
            $path = explode('/', $path);
            foreach ($path as $value) {
                if (isset($result[$value])) {
                    $result = $result[$value];
                } else {
                    return null;
                }
            }
        } else {
            $result = isset($result[$path]) ? $result[$path] : null;
        }
		return $result;
	}

    /**
     * Получить данные
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

	/**
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->data);
	}

    /**
	 * (non-PHPDoc)
     * @see ArrayAccess::indexOf
	 */
	public function indexOf($needle, $strict = false)
	{
		return in_array($needle, $this->data, $strict);
	}

    /**
     * Получить ключи данных
     *
     * @return array
     */
    public function keys()
    {
        if (!$this->data) {
            return array();
        }
        return array_keys($this->data);
    }

    /**
     * Соединить текущие данные с переданными
     *
     * @return Objective
     */
    public function merge($objective)
    {
        $this->data = array_merge($this->data, $objective->getData());
        return $this;
    }

    /**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

    /**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this->data[] = $value;
		} else {
			$this->__set($offset, $value);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset ($offset)
	{
		if (array_key_exists($offset, $this->data)) {
			unset($this->data[$offset]);
		}
	}

     /**
     * Прикрепить массив
     */
    public function set()
    {
        $argsCount = func_num_args();
        if ($argsCount == 1) {
            $this->setArray(func_get_arg(0));
        } elseif ($argsCount == 2) {
            list($key, $value) = func_get_args();
            $this->data[$key] = $value;
        }
    }

    public function setArray($array)
    {
        foreach ($array as $key => $value) {
            $this->data[$key] = $value;
        }
    }
}