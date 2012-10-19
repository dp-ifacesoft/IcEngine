<?php

/**
 * @desc Транслятор запросов типа create table для mysql
 * @author morph, goorus
 */
class Query_Translator_Mysql_Create_Table extends Query_Translator_Mysql_Alter_Table
{
	const SQL_CREATE_TABLE		= 'CREATE TABLE';
	const SQL_IF_NOT_EXISTS		= 'IF NOT EXISTS';
	const SQL_DEFAULT_CHARSET	= 'DEFAULT CHARSET';
	const SQL_ENGINE			= 'ENGINE';
	const SQL_COMMENT			= 'COMMENT';

	const DEFAULT_CHARSET		= 'utf8';
	const DEFAULT_ENGINE		= 'MyISAM';

	/**
	 * @desc Рендерит часть запроса default charset
	 * @param Query_Abstract $query
	 */
	public function _renderCharset (Query_Abstract $query)
	{
		$parts = $query->parts ();
		$charset = !empty ($parts [Query_Create_Table::DEFAULT_CHARSET])
			? $parts [Query_Create_Table::DEFAULT_CHARSET]
			: self::DEFAULT_CHARSET;
		return self::SQL_DEFAULT_CHARSET . '=' . $charset;
	}

	/**
	 * @desc Рендер части запроса create table
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderCreateTable (Query_Abstract $query)
	{
		$parts = $query->parts ();
		$name = $parts [Query_Create_Table::CREATE_TABLE];
		$sql = self::SQL_CREATE_TABLE . ' ' . self::SQL_IF_NOT_EXISTS . ' ' .
			$this->_escape (Model_Scheme::table ($name)) . '(';
		$fields = self::_renderFields ($query) ;
		return $sql . PHP_EOL .
			($fields ? $fields . ',' . PHP_EOL : '') .
			self::_renderIndexes ($query) . PHP_EOL . ') ' .
			self::_renderEngine ($query) . ' ' .
			self::_renderCharset ($query) . ' ' .
			self::_renderComment ($query);
	}

	/**
	 * @desc Рендерит часть запроса comment
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderComment (Query_Abstract $query)
	{
		$parts = $query->parts ();
		if (empty ($parts [Query_Create_Table::COMMENT]))
		{
			return;
		}
		return self::SQL_COMMENT . '=' .
			$this->_quote ($parts [Query_Create_Table::COMMENT]);
	}

	/**
	 * @desc Рендерит часть запроса engine
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderEngine (Query_Abstract $query)
	{
		$parts = $query->parts ();
		$engine = !empty ($parts [Query_Create_Table::ENGINE])
			? $parts [Query_Create_Table::ENGINE]
			: self::DEFAULT_ENGINE;
		return self::SQL_ENGINE. '=' . $engine;
	}

	/**
	 * @desc Рендерит поля
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderFields (Query_Abstract $query)
	{
		$parts = $query->parts ();
		$fields = $parts [Query_Create_Table::FIELD];
		if ($fields)
		{
			foreach ($fields as &$field)
			{
				$field = "\t" . $this->_renderField (
					$field [Query_Create_Table::NAME],
					$field [Query_Create_Table::ATTR]
				) . ',';
			}
			$fields = implode (PHP_EOL, $fields);
			$fields = substr ($fields, 0, -1);
			return $fields;
		}
	}

	/**
	 * @desc Рендерит игдексы
	 * @param Query_Abstract $query
	 * @return string
	 */
	public function _renderIndexes (Query_Abstract $query)
	{
		$parts = $query->parts ();
		$indexes = $parts [Query_Create_Table::INDEX];
		if ($indexes)
		{
			foreach ($indexes as &$index)
			{
				$index = $this->_renderIndex (
					$index [Query_Create_Table::NAME],
					$index
				) . ',';
			}
		}
		$indexes = implode (PHP_EOL, $indexes);
		$indexes = substr ($indexes, 0, -1);
		return $indexes;
	}
}