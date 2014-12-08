<?php

/**
 * Поле модели
 * 
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
	 * Атрибуты поля
	 * 
     * @var array
	 */
	protected $attrs;

	/**
	 * Имя поля
	 * 
     * @var string
	 */
	protected $name;

	/**
     * Конструктор
     * 
	 * @param string $name имя поля
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

    /**
	 * Получить атрибут по имени
	 * 
     * @return array
	 */
	public function getAttr($name)
	{
		return isset($this->attr[$name]) ? $this->attr[$name] : null;
	}
    
	/**
	 * Получить атрибуты поля
	 * 
     * @return array
	 */
	public function getAttrs()
	{
		return $this->attrs;
	}
    
    /**
     * Есть ли у поля флаг Auto_Increment
     * 
     * @return boolean
     */
    public function getAutoIncrement()
    {
        return !empty($this->attrs[self::ATTR_AUTO_INCREMENT]);
    }

    /**
     * Получить комментарии поля
     * 
     * @return string
     */
    public function getComment()
    {
        return $this->attrs[self::ATTR_COMMENT];
    }
    
    /**
     * Получить значение по умолчанию
     * 
     * @return mixed
     */
    public function getDefault()
    {
        return array_key_exists(self::ATTR_DEFAULT, $this->attrs)
            ? $this->attrs[self::ATTR_DEFAULT] : null;
    }
    
	/**
	 * Получить имя поля
	 * 
     * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
    
    /**
     * Получить флаг нулевости поля
     * 
     * @return boolean
     */
    public function getNullable()
    {
        return !empty($this->attrs[self::ATTR_NULL]);
    }
    
    /**
     * Получить размер поля
     * 
     * @return string
     */
    public function getSize()
    {
        return !empty($this->attrs[self::ATTR_SIZE])
            ? $this->attrs[self::ATTR_SIZE] : 0;
    }
    
    /**
     * Получить тип
     * 
     * @return string
     */
    public function getType()
    {
        return $this->attrs[self::ATTR_TYPE];
    }

    /**
     * Является ли поле unsigned
     * 
     * @return boolean
     */
    public function getUnsigned()
    {
        return !empty($this->attrs[self::ATTR_UNSIGNED]);
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
		$this->attrs[$attr] = $value;
		return $this;
	}
    
    /**
     * Изменить все атрибуты
     * 
     * @param array $attrs
     */
    public function setAttrs($attrs)
    {
        $this->attrs = $attrs;
    }

	/**
	 * Устанавливает атрибут поля "AUTO_INCREMENT"
	 * 
     * @param boolean $value
	 * @return Model_Field
	 */
	public function setAutoIncrement($value = true)
	{
		$this->attrs[self::ATTR_AUTO_INCREMENT] = (bool) $value;
		return $this;
	}

	/**
	 * Устанавливает атрибут поля "BINARY"
	 * 
     * @param boolean $value
	 * @return Model_Field
	 */
	public function setBinary($value = true)
	{
		$this->attrs[self::ATTR_BINARY] = (bool) $value;
		return $this;
	}

	/**
	 * Устанавливает атрибут "character set" полю
	 * 
     * @param string $charset
	 * @return Model_Field
	 */
	public function setCharset($charset)
	{
		$this->attrs[self::ATTR_CHARSET] = $charset;
		return $this;
	}

	/**
	 * Устанавливает атрибут поля "collate"
	 * 
     * @param string $collate
	 * @return Model_Field
	 */
	public function setCollate($collate)
	{
		$this->attrs[self::ATTR_COLLATE] = $collate;
		return $this;
	}

	/**
	 * Устанавливает атрибут "COMMENT" поля
	 * 
     * @param string $comment
	 * @return Model_Field
	 */
	public function setComment($comment)
	{
		$this->attrs[self::ATTR_COMMENT] = $comment;
		return $this;
	}

	/**
	 * Устанавливает атрибут поля "DEFAULT"
	 * 
     * @param string $value
	 * @return Model_Field
	 */
	public function setDefault($value)
	{
		$this->attrs[self::ATTR_DEFAULT] = $value;
		return $this;
	}

	/**
	 * Устанавливает атрибут поля "ENUM"
	 * 
     * @param string array
	 * @return Model_Field
	 */
	public function setEnum($values)
	{
		$this->attrs[self::ATTR_ENUM] = (array) $values;
		return $this;
	}

	/**
	 * Будет ли поле иметь атрибут "NULL", в противном случае -
	 * оно будет иметь атрибут NOT_NULL
	 * 
     * @param boolean $value
	 * @return Model_Field
	 */
	public function setNullable($value = true)
	{
		if ($value) {
			$this->attrs[self::ATTR_NULL] = true;
		} else {
			$this->attrs[self::ATTR_NOT_NULL] = true;
		}
		return $this;
	}

	/**
	 * Задать размер поля
	 * 
     * @param integer $size
	 * @return Model_Field
	 */
	public function setSize($size)
	{
		$this->attrs[self::ATTR_SIZE] = $size;
		return $this;
	}

	/**
	 * Задать тип поля
	 * 
     * @param string $type
	 * @return Model_Field
	 */
	public function setType($type)
	{
		$this->attrs[self::ATTR_TYPE] = $type;
		return $this;
	}

	/**
	 * Устанавливает атрибут поля "UNSIGNED"
	 * 
     * @param boolean $value
	 * @return Model_Field
	 */
	public function setUnsigned($value = true)
	{
		$this->attrs[self::ATTR_UNSIGNED] = (bool) $value;
		return $this;
	}
}