<?php

/**
 * @desc Транслятор запроса типа alter table для mysql
 * @author morph, goorus
 */
class Query_Translator_Mysql_Alter_Table extends Query_Translator_Abstract
{
	const SQL_ALTER_TABLE	= 'ALTER TABLE';
	const SQL_ADD			= 'ADD';
	const SQL_CHANGE		= 'CHANGE';
	const SQL_DROP			= 'DROP';

	/**
	 * @see Helper_Mysql::escape()
	 */
	public function _escape ($value)
	{
		return Helper_Mysql::escape ($value);
	}

	/**
	 * @see Helper_Mysql::quote()
	 */
	public function _quote ($value)
	{
		return Helper_Mysql::quote ($value);
	}

	/**
	 * @desc Получение типа индекса
	 * @staticvar array $types
	 * @param string $type
	 * @return string
	 */
	public function _indexType ($type)
	{
		static $types = array (
			'key'		=> 'Key',
			'index'		=> 'Index',
			'primary'	=> 'Primary key',
			'unique'	=> 'Unique key'
		);
		return $types [strtolower ($type)];
	}

	/**
	 * @desc Рендеринг части запроса add
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderAdd (Query_Abstract $query)
	{
		$parts = $query->parts ();
		if (empty ($parts [Query_Alter_Table::ADD]))
		{
			return;
		}
		$sql = ' ' . self::SQL_ADD . ' ';
		$name = $parts [Query_Alter_Table::ADD][Query_Alter_Table::FIELD];
		if (!empty ($parts [Query_Alter_Table::ATTR]))
		{
			$sql .= self::_renderField ($name, $parts [Query_Alter_Table::ATTR]);
		}
		elseif (!empty ($parts [Query_Alter_Table::INDEX]))
		{
			$sql .= self::_renderIndex ($name, $parts [Query_Alter_Table::INDEX]);
		}
		return $sql;
	}

	/**
	 * @desc Рендеринг части запроса alter table
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderAlterTable (Query_Abstract $query)
	{
		$parts = $query->parts ();
		$table = $parts [Query_Alter_Table::TABLE];
		$sql = self::SQL_ALTER_TABLE . ' ' .
			$this->_escape ($table) . ' ' .
			self::_renderAdd ($query) .
			self::_renderChange ($query) .
			self::_renderDrop ($query);
		return $sql;
	}

	/**
	 * @desc Рендеринг части запроса change
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderChange (Query_Abstract $query)
	{
		$parts = $query->parts ();
		if (empty ($parts [Query_Alter_Table::CHANGE]))
		{
			return;
		}
		$name = $parts [Query_Alter_Table::CHANGE][Query_Alter_Table::FIELD];
		$sql = ' ' . self::SQL_CHANGE . ' ' . $this->_escape ($name) . ' ';
		$sql .= self::_renderField (
			$parts [Query_Alter_Table::ATTR][Query_Alter_Table::NAME],
			$parts [Query_Alter_Table::ATTR]
		);
		return $sql;
	}

	/**
	 * @desc Рендеринг части запроса drop
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderDrop (Query_Abstract $query)
	{
		$parts = $query->parts ();
		if (empty ($parts [Query_Alter_Table::DROP]))
		{
			return;
		}
		$name = $parts [Query_Alter_Table::DROP][Query_Alter_Table::FIELD];
		$sql = self::SQL_DROP . ' ';
		if (!empty ($parts [Query_Alter_Table::INDEX]))
		{
			$index = $parts [Query_Alter_Table::INDEX];
			$sql .= ' ' .
				strtoupper (self::_indexType ($index [Query_Alter_Table::TYPE])) .
				' ';
		}
		$sql .= $this->_escape ($name);
		return $sql;
	}

	/**
	 * @desc Рендеринг индекса
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public function _renderIndex ($name, $params)
	{
		$sql = strtoupper (self::_indexType ($params [Query_Alter_Table::TYPE])) .
			' ' . $this->_escape ($name) . '(';
		$fields = $params [Query_Alter_Table::FIELD];
		foreach ($fields as &$field)
		{
			$field = $this->_escape ($field);
		}
		$sql .= implode (',', $fields) . ')';
		return $sql;
	}

	/**
	 * @desc Рендеринг поля
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public function _renderField ($name, $params)
	{
		$type = $params [Model_Field::ATTR_TYPE];
		$sql = $this->_escape ($name) . ' ' . strtoupper ($type);
		if (
			strpos (strtolower ($type), 'text') === false &&
			strpos (strtolower ($type), 'date') === false &&
			strpos (strtolower ($type), 'time') === false
		)
		{
			if (
				!empty ($params [Model_Field::ATTR_ENUM]) ||
				!empty ($params [Model_Field::ATTR_SIZE])
			)
			{
				$sql .= '(';
				if (strpos (strtolower ($type), 'enum') !== false)
				{
					$enum = $params [Model_Field::ATTR_ENUM];
					foreach ($enum as &$e)
					{
						$e = $this->_quote ($e);
					}
					$sql .= implode (',', $enum);
				}
				else
				{
					$sql .= implode (',', (array)
						$params [Model_Field::ATTR_SIZE]);
				}
				$sql .= ')';
			}
		}
		if (!empty ($params [Model_Field::ATTR_UNSIGNED]))
		{
			$sql .= ' ' . Model_Field::ATTR_UNSIGNED . ' ';
		}
		if (!empty ($params [Model_Field::ATTR_BINARY]))
		{
			$sql .= ' ' . Model_Field::ATTR_BINARY . ' ';
		}
		if (empty ($params [Model_Field::ATTR_NULL]))
		{
			$sql .= ' ' . Model_Field::ATTR_NOT_NULL . ' ';
		}
		else
		{
			$sql .= ' ' . Model_Field::ATTR_NULL . ' ';
		}
		if (
			strpos (strtolower ($type), 'text') !== false ||
			strpos (strtolower ($type), 'char') !== false
		)
		{
			if (!empty ($params [Model_Field::ATTR_CHARSET]))
			{
				$sql .= ' ' . Model_Field::ATTR_CHARSET . ' ' .
					$params [Model_Field::ATTR_CHARSET];
			}
			if (!empty ($params [Model_Field::ATTR_COLLATE]))
			{
				$sql .= ' ' . Model_Field::ATTR_COLLATE . ' ' .
					$params [Model_Field::ATTR_COLLATE];
			}
		}

		if (isset ($params [Model_Field::ATTR_DEFAULT]))
		{
			if (empty ($params [Model_Field::ATTR_AUTO_INCREMENT]))
			{
				$default = $params [Model_Field::ATTR_DEFAULT];
				if (!empty ($params [Model_Field::ATTR_NULL]))
				{
					if (!$params [Model_Field::ATTR_DEFAULT])
					{
						$default = 'NULL';
					}
				}

				if (
					strpos (strtolower ($type), 'int') !== false ||
					strpos (strtolower ($type), 'float') !== false ||
					strpos (strtolower ($type), 'double') !== false ||
					strpos (strtolower ($type), 'real') !== false ||
					strpos (strtolower ($type), 'decimal') !== false
				)
				{
					if ($default != 'NULL')
					{
						$default = (int) $default;
					}
				}

				if (strpos (strtolower ($type), 'text') === false)
				{
					if ($default != 'NULL')
					{
						$default = $this->_quote ($default);
					}
					$sql .= ' ' . Model_Field::ATTR_DEFAULT . ' ' .
						$default;
				}
			}
		}
		else
		{
			if (empty ($params [Model_Field::ATTR_AUTO_INCREMENT]))
			{
				if (!empty ($params [Model_Field::ATTR_NULL]))
				{
					$sql .= ' ' . Model_Field::ATTR_DEFAULT . ' NULL';
				}
			}
		}
		if (!empty ($params [Model_Field::ATTR_AUTO_INCREMENT]))
		{
			$sql .= ' ' . Model_Field::ATTR_AUTO_INCREMENT . ' ';
		}
		if (!empty ($params [Model_Field::ATTR_COMMENT]))
		{
			$sql .= ' ' . Model_Field::ATTR_COMMENT . ' ' .
				$this->_quote ($params [Model_Field::ATTR_COMMENT]);
		}
		return $sql;
	}
}