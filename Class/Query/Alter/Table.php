<?php

/**
 * @desc Конструктор для запросов класса "ALTER TABLE"
 * @author morph
 */
class Query_Alter_Table extends Query_Abstract
{
	const ADD		= 'ADD';
	const ATTR		= '__ATTR__';
	const CHANGE	= 'CHANGE';
	const DROP		= 'DROP';
	const FIELD		= '__FIELD__';
	const INDEX		= '__INDEX__';
	const TABLE		= '__TABLE__';
	const TYPE		= '__TYPE__';
	const NAME		= '__NAME__';
	const ALTER_TABLE = 'ALTER TABLE';

	/**
	 * @see Query_Abstract::$_defaults
	 */
	public static $_defaults = array (
		self::ADD			=> array (),
		self::ALTER_TABLE   => array (),
		self::ATTR			=> array (),
		self::CHANGE		=> array (),
		self::DROP			=> array (),
		self::INDEX			=> array (),
	);

	/**
	 * @desc Получить атрибуты поля
	 * @param Model_Field $field
	 * @return array
	 */
	protected function _attrs (Model_Field $field)
	{
		$result = array ();
		foreach ($field->getAttrs () as $key => $value)
		{
			$result [$key] = $value;
		}
		return $result;
	}

	/**
	 * @desc Добавляет индекс
	 * @param Model_Index $index
	 * @return Query_Alter_Table
	 */
	protected function index ($index)
	{
		$this->_parts [self::INDEX] = array (
			self::NAME	=> $index->getName (),
			self::TYPE	=> $index->getType (),
			self::FIELD	=> $index->getFields ()
		);
		return $this;
	}

	/**
	 * @desc Часть запроса ADD
	 * @param $field
	 * @return Query_Alter_Table
	 */
	public function add ($field)
	{
		$this->_parts [self::ADD] = array (
			self::FIELD => $field->getName ()
		);

		if ($field instanceof Model_Field)
		{
			$this->_parts [self::ATTR] = $this->_attrs ($field);
		}
		elseif ($field instanceof Model_Index)
		{
			$this->index ($field);
		}
		return $this;
	}

	/**
	 * @desc Часть запроса ALTER
	 * @param string $table имя таблицы
	 * @return Query_Alter_Table
	 */
	public function alterTable ($table)
	{
		$this->_parts [self::TABLE] = $table;
		return $this;
	}

	/**
	 * @desc Часть запроса "CHANGE"
	 * @param string $old_name старое имя поля
	 * @param Model_Field $new_field  новое поле
	 * @return Query_Alter_Table
	 */
	public function change ($old_name, Model_Field $new_field)
	{
		$this->_parts [self::CHANGE] = array (
			self::FIELD => $old_name
		);
		$this->_parts [self::ATTR] = array_merge (
			array (
				self::NAME => $new_field->getName ()
			),
			$this->_attrs ($new_field)
		);
		return $this;
	}

	/**
	 * @desc Часть запроса "DROP"
	 * @param $field
	 * @return Query_Alter_Table
	 */
	public function drop ($field)
	{
		$this->_parts [self::DROP] = array (
			self::FIELD => $field->getName ()
		);
		if ($field instanceof Model_Index)
		{
			$this->index ($field);
		}
		return $this;
	}

	/**
	 * @see Query::reset()
	 */
	public function reset ()
	{
		$this->_type = Query::INSERT;
		return parent::reset ();
	}
}