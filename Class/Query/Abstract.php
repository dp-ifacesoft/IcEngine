<?php

/**
 * @desc Общий запрос
 * @author Илья Колесников, Юрий Шведов
 */
class Query_Abstract
{
	/**
	 * @desc Части запроса по умолчанию.
	 * @var array
	 */
	public static $_defaults;

	/**
	 * @desc Части запроса
	 * @var array
	 */
	protected $_parts;

	/**
	 * @desc Тип запроса
	 * @var string
	 */
	protected $_type;

	/**
	 * @desc Возвращает новый пустой запрос.
	 */
	public function __construct()
	{
		$this->reset ();
	}

	/**
	 * @desc Преобразует части запроса в строку
	 * @return string
	 */
	public function __toString ()
	{
		return $this->translate ();
	}

	/**
	 * Добавить часть запроса
	 *
	 * @return Query_Abstract
	 */
	public function addPart($parts)
	{
		$args = func_get_args();
		$modelName = null;
		$from = $this->getPart(Query::FROM);
		if ($from) {
			$from = reset($from);
			$modelName = $from[Query::TABLE];
		}
		foreach ($args as $arg) {
			$name = $arg;
			$params = array();
			if (is_array($arg)) {
				list($name, $params) = $arg;
			}
			$className = 'Query_Part_' . $name;
			$part = new $className($modelName, $params);
			$part->inject($this);
		}
		return $this;
	}

	/**
	 * @desc Возвращает имя запроса
	 * @return string
	 */
	public function getName ()
	{
		return substr (
			get_class ($this),
			strlen ('Query_')
		);
	}

	/**
	 * @desc Возвращает часть запроса
	 * @param string $name
	 * @return mixed
	 */
	public function getPart ($name)
	{
		return isset ($this->_parts [$name]) ? $this->_parts [$name] : null;
	}

	/**
	 * @desc Возвращает тэги
	 * @return array
	 */
	public function getTags ()
	{

	}

	/**
	 * @desc Возвращает часть запроса
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function part ($name, $default = null)
	{
		return isset ($this->_parts [$name]) ? $this->_parts [$name] : $default;
	}

	/**
	 * @desc Возвращает все части запроса.
	 * @return array
	 */
	public function parts ()
	{
		return $this->_parts;
	}

	/**
	 * @desc Сброс всех частей запроса.
	 * @return Query Этот запрос.
	 */
	public function reset ()
	{
		$this->_parts = static::$_defaults;
		return $this;
	}

	/**
	 * @desc Сбрасывает часть запроса
	 * @param string|array $parts
	 * @return Query Этот запрос.
	 */
	public function resetPart ($parts)
	{
		if (!is_array ($parts))
		{
			$parts = func_get_args ();
		}

		foreach ($parts as $part)
		{
			if (isset (self::$_defaults [$part]))
			{
				$this->_parts [$part] = self::$_defaults [$part];
			}
			else
			{
				unset ($this->_parts [$part]);
			}
		}

		return $this;
	}

	/**
	 * @desc Подменяет часть запроса
	 * @param string $name Часть запроса.
	 * @param mixed $value Новое значение.
	 * @return Query Этот запрос.
	 */
	public function setPart ($name, $value)
	{
		$this->_parts [$name] = $value;
		return $this;
	}

	/**
	 * @desc Транслирует запрос указанным транслятором
	 * @param string $translator Транслятор.
	 * @return mixed Транслированный запрос.
	 */
	public function translate ($translator = 'Mysql')
	{
		return Query_Translator::byName ($translator . '_' . $this->getName ())
			->translate ($this);
	}

	/**
	 * @desc Тип запроса
	 * @return string
	 */
	public function type ()
	{
		return $this->_type;
	}

	/**
	 * @desc Установка значений для INSERT/UPDATE
	 * @param array $values
	 * @return Query Этот запрос.
	 */
	public function values (array $values)
	{
		if (isset ($this->_parts [Query::VALUES]))
		{
			$this->_parts [Query::VALUES] = array_merge (
				$this->_parts [Query::VALUES],
				$values
			);
		}
		else
		{
			$this->_parts [Query::VALUES] = $values;
		}
		return $this;
	}
}