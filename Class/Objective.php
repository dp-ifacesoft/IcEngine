<?php
/**
 *
 * @desc Объект с динамически создаваемыми полями.
 * Может быть использован как массив.
 * @author Morph
 * @package IcEngine
 *
 */

class Objective implements ArrayAccess, IteratorAggregate, Countable
{

	/**
	 * @desc Данные объекта.
	 * @var array
	 */
	protected $_data = array ();

	/**
	 * @desc Возвращает экземпляр Objective
	 * @param array $data
	 */
	public function __construct (array $data = array ())
	{
		// Переносим все поля класса в массив _data
		$vars = get_class_vars (get_class ($this));

		foreach ($vars as $key => $value)
		{
			if ($key [0] != '_')
			{
				$this->_data [$key] = $value;
				unset ($this->$key);
			}
		}

		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}
	}

	/**
	 * @desc Клонирование (выполняется рекурсивно)
	 */
	public function __clone ()
	{
		$data = array ();

		foreach ($this->_data as $key => $value)
		{
			if ($value instanceof Objective)
			{
				$data [$key] = clone $value;
			}
			else
			{
				$data [$key] = $value;
			}
		}

		$this->_data = $data;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function __get ($key)
	{
		return isset ($this->_data [$key]) ? $this->_data [$key] : null;
	}

	/**
	 * @desc Проверяет существование поля.
	 * @param string $key
	 * @return boolean
	 */
	public function __isset ($key)
	{
		return isset ($this->_data [$key]);
	}

	/**
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set ($key, $value)
	{
		if (is_array ($value))
		{
			$this->_data [$key] = new self ($value);
		}
		else
		{
			$this->_data [$key] = $value;
		}
	}

	/**
	 * @desc Данные объекта в виде массива.
	 * Данные типа Objective рекурсивно будут приведены к массивам.
	 * @return array
	 */
	public function __toArray ()
	{
		$data = array ();

		foreach ($this->_data as $key => $value)
		{
			if ($value instanceof Objective)
			{
				$data [$key] = $value->__toArray ();
			}
			else
			{
				$data [$key] = $value;
			}
		}

		return $data;
	}

	/**
	 * @desc Данные объекта как массив.
	 * Если существуют данные типа Objective, они будут переданны как
	 * объект без приведения к массиву (в отличие от __toArray)
	 * @return array
	 */
	public function asArray ()
	{
		return $this->_data;
	}

	/**
	 *
	 * @desc Получить колонку объекта
	 * @param string $column
	 * @return array<string>
	 */
	public function column ($column)
	{
		$result = array ();

		foreach ($this as $item)
		{
			$result [] = ($item instanceof Objective)
					? $item->$column : null;
		}

		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see Countable::count()
	 */
	public function count ()
	{
		return count ($this->_data);
	}

	/**
	 * Проверка на существование поля.
	 * @param string $key
	 * @return boolean
	 */
	public function exists ($key)
	{
		return isset ($this->_data [$key]);
	}

	/**
	 * @param string $path
	 * @return mixed
	 */
	public function get ()
	{
		$result = $this->__toArray ();
		if (func_num_args () == 1)
		{
			$path = func_get_arg (0);

			if (strpos ($path, '/'))
			{
				$path = explode ('/', $path);
				foreach ($path as $value)
				{
					if (isset ($result [$value]))
					{
						$result = $result [$value];
					}
					else
					{
						return null;
					}
				}
			}
			else
			{
				$result = isset ($result [$path]) ? $result [$path] : null;
			}
		}
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator ()
	{
		return new ArrayIterator ($this->_data);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet ($offset, $value)
	{
		if (is_null ($offset))
		{
			$this->_data [] = $value;
		}
		else
		{
			$this->__set ($offset, $value);
		}
	}

	/**
	 * Checks if a value exists in an array
	 * @link http://www.php.net/manual/en/function.in-array.php
	 * @param needle mixed <p>
	 * The searched value.
	 * </p>
	 * <p>
	 * If needle is a string, the comparison is done
	 * in a case-sensitive manner.
	 * </p>
	 * @param strict bool[optional] <p>
	 * If the third parameter strict is set to true
	 * then the in_array function will also check the
	 * types of the
	 * needle in the haystack.
	 * </p>
	 * @return bool true if needle is found in the array,
	 * false otherwise.
	 */
	public function indexOf ($needle, $strict = false)
	{
		return in_array ($needle, $this->_data, $strict);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists ($offset)
	{
		return isset ($this->_data [$offset]);
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset ($offset)
	{
		if (array_key_exists ($offset, $this->_data))
		{
			unset ($this->_data [$offset]);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet ($offset)
	{
		return $this->__get ($offset);
	}

}