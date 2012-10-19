<?php

class Controller_Cli_Scheme extends Controller_Abstract
{
	public function export ()
	{
		Controller_Manager::call (
			'Redis_Clear',
			'clearContent'
		);

		$model = $this->_input->receive ('model');

		$scheme = Model_Scheme::getScheme ($model);

		$prefix = Model_Scheme::$defaultPrefix;

		$sql = 'CREATE TABLE `' . $prefix . strtolower ($model) . '` (' . "\r\n";

		foreach ($scheme ['fields'] as $field => $data)
		{
			if (!$data)
			{
				continue;
			}

			$tmp = '`' . $field . '` ' . $data ['type'];

			if (!empty ($data ['size']))
			{
				$tmp .= '(' . $data ['size'] . ')';
			}

			$tmp .= ' NOT NULL';

			if (!empty ($data ['auto_int']))
			{
				$tmp .= ' AUTO_INCREMENT';
			}

			if (isset ($data ['default']))
			{
				$tmp .= ' DEFAULT \'';
				if ($data ['default'] !== '')
				{
					$tmp .= addslashes ($data ['default']);
				}
				$tmp .= '\'';
			}

			if (!empty ($data ['comment']))
			{
				$tmp .= ' COMMENT \'' . addslashes ($data ['comment']) . '\'';
			}

			$tmp .= ',' . "\r\n";

			$sql .= $tmp;
		}

		$keys = array (
			'primary'	=> 'PRIMARY',
			'unique'	=> 'UNIQUE',
			'index'		=> ''
		);

		foreach ($scheme ['keys'] as $key)
		{
			$type = key ($key);
			$fields = (array) current ($key);

			if (!$fields)
			{
				continue;
			}

			$tmp = $keys [$type] . ' KEY ';
			$tmp .= '`' . implode ('_', $fields) . '` (';

			$f = '';

			foreach ($fields as $field)
			{
				$f .= '`' . $field . '`,';
			}

			$f = substr ($f, 0, -1);

			$tmp .= $f . '),' . "\r\n";

			$sql .= $tmp;
		}

		$sql = substr ($sql, 0, -3) . "\r\n";

		$sql .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8';

		mysql_query ($sql);
	}

	public function synsField ()
	{
		Controller_Manager::call (
			'Redis_Clear',
			'clearContent'
		);

		$model = $this->_input->receive ('model');

		$table = Model_Scheme::table ($model);

		$fields = Helper_Data_Source::fields ('`' . $table . '`');
		$names_db = $fields->column ('Field');

		$scheme = Model_Scheme::getScheme ($model);

		$names_scheme = array_keys ($scheme ['fields']);

		$diff_names = array_diff ($names_db, $names_scheme);

		foreach ($diff_names as $name)
		{
			if (!isset ($names_scheme [$name]))
			{
				continue;
			}

			$field = $names_scheme [$name];

			$sql = 'ALTER TABLE `' . $table . '` ADD `' . $name . '` '  .
				$field ['type'];

			if (!empty ($field ['size']))
			{
				$sql .= '(' . $field ['size'] . ')';
			}

			$sql .= 'NOT NULL';

			if (isset ($field ['default']))
			{
				$sql .= ' DEFAULT \'';
				if ($field ['default'] !== '')
				{
					$sql .= addslashes ($field ['default']);
				}
				$sql .= '\'';
			}

			mysql_query ($sql);
		}
	}
}