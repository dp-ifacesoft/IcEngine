<?php

/**
 * @desc запрос типа create table
 * @author morph, goorus
 */
class Query_Create_Table extends Query_Alter_Table
{
	const CREATE_TABLE		= 'CREATE TABLE';
	const TABLE				= 'TABLE';
	const IF_NOT_EXISTS		= 'IF NOT EXISTS';
	const ENGINE			= 'ENGINE';
	const DEFAULT_CHARSET	= 'DEFAULT CHARSET';
	const FIELD				= '__FIELD__';
	const COMMENT			= 'COMMENT';

	/**
	 * @see Query_Abstract::$_defaults
	 */
	public static $_defaults = array (
		self::FIELD				=> array (),
		self::INDEX				=> array (),
		self::ENGINE			=> null,
		self::DEFAULT_CHARSET	=> null,
		self::TABLE				=> null,
		self::COMMENT			=> null
	);

	/**
	 * @desc Добавляет поле
	 * @param Model_Field $field
	 * @return Query_Create_Table
	 */
	public function addField (Model_Field $field)
	{
		$this->_parts [self::FIELD][] = array (
			self::NAME		=> $field->getName (),
			self::ATTR		=> $this->_attrs ($field)
		);
		return $this;
	}

	/**
	 * @desc Добавить массив полей
	 * @param array $fields
	 * @return Query_Create_Table
	 */
	public function addFields (array $fields)
	{
		foreach ($fields as $field)
		{
			if ($field instanceof Model_Field)
			{
				$this->addField ($field);
			}
		}
		return $this;
	}

	/**
	 * @desc Добавляет индекс
	 * @param Model_Index $index
	 * @return Query_Create_Table
	 */
	public function addIndex (Model_Index $index)
	{
		$this->_parts [self::INDEX][] = array (
			self::NAME	=> $index->getName (),
			self::TYPE	=> $index->getType (),
			self::FIELD => $index->getFields ()
		);
		return $this;
	}

	/**
	 * @desc Добавить массив индексов
	 * @param array $indexes
	 * @return Query_Create_Table
	 */
	public function addIndexes (array $indexes)
	{
		foreach ($indexes as $index)
		{
			if ($index instanceof Model_Index)
			{
				$this->addIndex ($index);
			}
		}
		return $this;
	}

	/**
	 * @desc Часть запроса CREATE TABLE
	 * @param string $table
	 * @return Query_Create_Table
	 */
	public function createTable ($table)
	{
		$this->_parts [self::CREATE_TABLE] = $table;
		return $this;
	}

	/**
	 * @see Query_Abstract::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::INSERT;
		return $this;
	}

	/**
	 * @desc Изменить коммментарий модели
	 * @param string $comment
	 * @return Query_Create_Table
	 */
	public function setComment ($comment)
	{
		$this->_parts [self::COMMENT] = $comment;
		return $this;
	}

	/**
	 * @desc Меняет атрибут таблицы ENGINE
	 * @param string $engine
	 * @return Query_Create_Table
	 */
	public function setEngine ($engine)
	{
		$this->_parts [self::ENGINE] = $engine;
		return $this;
	}

	/**
	 * @desc Меняет кодировку по умолчанию
	 * @param string $charset
	 * @return Query_Create_Table
	 */
	public function setCharset ($charset)
	{
		$this->_parts [self::DEFAULT_CHARSET] = $charset;
		return $this;
	}
}