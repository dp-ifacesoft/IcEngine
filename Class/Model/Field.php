<?php

/**
 * @desc Поле модели
 * @author morph
 */
class Model_Field
{
	const ATTR			 = '__ATTR__';
	const ATTR_AUTO_INCREMENT = 'AUTO_INCREMENT';
	const ATTR_BINARY	 = 'BINARY';
	const ATTR_COLLATE	 = 'COLLATE';
	const ATTR_COMMENT   = 'COMMENT';
	const ATTR_CHARSET   = 'CHARACTER SET';
	const ATTR_DEFAULT	 = 'DEFAULT';
	const ATTR_ENUM		 = 'ENUM';
	const ATTR_NOT_NULL  = 'NOT NULL';
	const ATTR_NULL		 = 'NULL';
	const ATTR_SIZE		 = '__SIZE__';
	const ATTR_TYPE		 = '__TYPE__';
	const ATTR_UNSIGNED  = 'UNSIGNED';

	/**
	 * @desc Атрибуты поля
	 * @var array
	 */
	protected $_attrs;

	/**
	 * @desc Имя поля
	 * @var string
	 */
	protected $_name;

	/**
	 * @param string $name имя поля
	 */
	public function __construct ($name)
	{
		$this->_name = $name;
	}

	/**
	 * @desc Получить атрибуты поля
	 * @return array
	 */
	public function getAttrs ()
	{
		return $this->_attrs;
	}

	/**
	 * @desc Получить имя поля
	 * @return string
	 */
	public function getName ()
	{
		return $this->_name;
	}

	/**
	 * Изменить значение атрибута
	 *
	 * @param string $attr
	 * @param string $value
	 * @return Model_Field
	 */
	public function setAttr($attr, $value)
	{
		$this->_attrs[$attr] = $value;
		return $this;
	}

	/**
	 * @desc Устанавливает атрибут поля "AUTO_INCREMENT"
	 * @param boolean $value
	 * @return Model_Field
	 */
	public function setAutoIncrement ($value = true)
	{
		$this->_attrs [self::ATTR_AUTO_INCREMENT] = (bool) $value;
		return $this;
	}

	/**
	 * @desc Устанавливает атрибут поля "BINARY"
	 * @param boolean $value
	 * @return Model_Field
	 */
	public function setBinary ($value = true)
	{
		$this->_attrs [self::ATTR_BINARY] = (bool) $value;
		return $this;
	}

	/**
	 * @desc Устанавливает атрибут "character set" полю
	 * @param string $charset
	 * @return Model_Field
	 */
	public function setCharset ($charset)
	{
		$this->_attrs [self::ATTR_CHARSET] = $charset;
		return $this;
	}

	/**
	 * @desc Устанавливает атрибут поля "collate"
	 * @param string $collate
	 * @return Model_Field
	 */
	public function setCollate ($collate)
	{
		$this->_attrs [self::ATTR_COLLATE] = $collate;
		return $this;
	}

	/**
	 * @desc Устанавливает атрибут "COMMENT" поля
	 * @param string $comment
	 * @return Model_Field
	 */
	public function setComment ($comment)
	{
		$this->_attrs [self::ATTR_COMMENT] = $comment;
		return $this;
	}

	/**
	 * @desc Устанавливает атрибут поля "DEFAULT"
	 * @param string $value
	 * @return Model_Field
	 */
	public function setDefault ($value)
	{
		$this->_attrs [self::ATTR_DEFAULT] = $value;
		return $this;
	}

	/**
	 * @desc Устанавливает атрибут поля "ENUM"
	 * @param string array
	 * @return Model_Field
	 */
	public function setEnum ($values)
	{
		$this->_attrs [self::ATTR_ENUM] = (array) $values;
		return $this;
	}

	/**
	 * @desc Будет ли поле иметь атрибут "NULL", в противном случае -
	 * оно будет иметь атрибут NOT_NULL
	 * @param boolean $value
	 * @return Model_Field
	 */
	public function setNullable ($value = true)
	{
		if ($value)
		{
			$this->_attrs [self::ATTR_NULL] = true;
		}
		else
		{
			$this->_attrs [self::ATTR_NOT_NULL] = true;
		}
		return $this;
	}

	/**
	 * @desc Задать размер поля
	 * @param integer $size
	 * @return Model_Field
	 */
	public function setSize ($size)
	{
		$this->_attrs [self::ATTR_SIZE] = (int) $size;
		return $this;
	}

	/**
	 * @desc Задать тип поля
	 * @param string $type
	 * @return Model_Field
	 */
	public function setType ($type)
	{
		$this->_attrs [self::ATTR_TYPE] = $type;
		return $this;
	}

	/**
	 * @desc Устанавливает атрибут поля "UNSIGNED"
	 * @param boolean $value
	 * @return Model_Field
	 */
	public function setUnsigned ($value = true)
	{
		$this->_attrs [self::ATTR_UNSIGNED] = (bool) $value;
		return $this;
	}
}