<?php

/**
 * @desc Транслятор для key-value хранилищь
 */

class Query_Translator_KeyValue_Select extends Query_Translator_Abstract
{

	/**
	 * Разделитель таблицы и индкса.
	 * @var string
	 */
	public $tableIndexDelim = '_';

	/**
	 * Разделитель индекса и первичного ключа.
	 * @var string
	 */
	public $indexKeyDelim = '/';

	/**
	 * Разделитель значений индексов.
	 * @var string
	 */
	public $valuesDelim = ':';

	/**
	 * @desc Возвращает массив масок ключей
	 * @param string $table Таблица (модель).
	 * @param array $where Часть запроса Query::WHERE
	 * @return array Массив масок
	 */
	protected function _compileKeyMask ($table, array $where)
	{
		$key_field = Model_Scheme::keyField ($table);

		$indexes = Model_Scheme::indexes ($table);

		// Покрытие индексом запроса
		// Изначально строка "11111", по мере использования,
		// 1 заменяются на 0. Если индекс покрывает запрос, в конце
		// значение будет равно "000000" == 0
		$index_use = array ();

		// Значения для полей индекса
		$index_values = array ();

		$keys = array_keys ($indexes);

		// Отсекаем индексы, которые заведомо не покрывают запрос (короткие)
		// и инициализируем массивы
		foreach ($indexes as $i => $index)
		{
			if (count ($index) < count ($where))
			{
				unset ($indexes [$i]);
			}
			else
			{
				$index_use [$i] = str_repeat ('1', count ($index));
				$index_values [$i] = array ();
			}
		}

		// Запоминаем значения для полей индекса
		foreach ($where as $wi => &$wvalue)
		{
			$cond = $wvalue [Query::WHERE];

			if (!is_scalar ($wvalue [Query::VALUE]))
			{
				throw new Zend_Exception ('Condition unsupported.');
			}

			if (!is_array ($cond))
			{
				// Получаем таблицу и колонку
				$cond = explode ('.', $cond, 2);
			}

			if (empty ($cond))
			{
				throw new Zend_Exception ('Condition field unsupported.');
			}

			$cond = trim (end ($cond), '`?= ');
			$is_like = (strtoupper (substr ($cond, -4, 4)) == 'LIKE');
			$where_value = urlencode ($wvalue [Query::VALUE]);

			if (!$is_like && $cond == $key_field)
			{
				return array (
					$table . $this->tableIndexDelim .
					'k' . $this->indexKeyDelim .
					$where_value
				);
			}

			foreach ($indexes as $ii => &$icolumns)
			{
				foreach ($icolumns as $ici => &$icolumn)
				{
					if ($cond == $icolumn)
					{
						$index_use [$ii][$ici] = 0;

						if ($is_like)
						{
							$index_values [$ii][$ici] = str_replace (
								'%25', '*', $where_value
							);
						}
						else
						{
							$index_values [$ii][$ici] = $where_value;
						}
					}
				}
				unset ($icolumn);
			}
			unset ($icolumns);
		}

		// Выбираем наиболее покрывающий индекс.
		$best_v = 0;
		$best_i = 0;
		foreach ($index_use as $ii => $use)
		{
			$v = strpos ($use, '1');

			if ($v === false)
			{
				// Индекс полностью покрывает запрос
				return array ($this->_pattern (
					$table, $ii, $use, $index_values [$ii]
				));
			}

			if ($v > $best_v)
			{
				$best_v = $v;
				$best_i = $ii;
			}
		}

		if ($best_v >= 0)
		{
			return array ($this->_pattern (
				$table, $best_i,
				$index_use [$best_i], $index_values [$best_i]
			));
		}

		return array ();
	}

	/**
	 * Формирование ключей для записи.
	 * @param string $table
	 * @param array $values
	 * @return array
	 */
	public function _compileKeys ($table, array $values)
	{
		$key_field = Model_Scheme::keyField ($table);

		if (!isset ($values [$key_field]))
		{
			throw new Zend_Exception ("Primary key must be defined.");
		}

		$keys = array (
			$table . $this->tableIndexDelim .
			'k' . $this->indexKeyDelim .
			$values [$key_field]
		);

		$indexes = Model_Scheme::indexes ($table);
		foreach ($indexes as $i => $index)
		{
			$index = (array) $index;
			$vals = array ();
			foreach ($index as $name)
			{
				$vals [] = urlencode ($values [$name]);
			}
			$vals [] = urlencode ($values [$key_field]);

			$keys [] =
				$table . $this->tableIndexDelim .
				$i . $this->indexKeyDelim .
				implode ($this->valuesDelim, $vals);
		}

		return $keys;
	}

	/**
	 * Извлекает имя таблицы из запроса.
	 * @param Query_Abstract $query
	 * @return string
	 * @throws Zend_Exception
	 */
	public function extractTable (Query_Abstract $query)
	{
		$tables = $query->part (Query::FROM);

		// Отдельно хранятся таблицы для INSERT и UPDATE
		$type = $query->type ();
		if ($type == Query::INSERT || $type == Query::UPDATE)
		{
			return $query->part ($type);
		}

		// Иначе SELECT или DELETE
		if (count ($tables) != 1)
		{
			throw new Zend_Exception ('Invalid query.');
		}

		$table = reset ($tables);
		return $table [Query::TABLE];
	}

	/**
	 * @desc Извлекает первичный ключ записи из ключа кэша.
	 * @param string $key
	 * @return string
	 */
	public function extractId ($key)
	{
		$id = substr (
			strrchr (
				$key,
				$this->valuesDelim
			),
			1
		);

		if (!$id)
		{
			$id = substr (
				strrchr (
					$key,
					$this->indexKeyDelim
				),
				1
			);
		}

		return $id;
	}

	/**
	 *
	 * @param string $table
	 * @param integer $index_num
	 * @param string $index_use
	 * @param array $values
	 * @return string
	 */
	protected function _pattern ($table, $index_num, $index_use, array $values)
	{
		$pattern =
			$table . $this->tableIndexDelim .
			$index_num . $this->indexKeyDelim;

		for ($i = 0; $i < strlen ($index_use); $i++)
		{
			if (array_key_exists ($i, $values))
			{
				$pattern .= $values [$i];
			}
			else
			{
				$pattern .= '*';
			}
			$pattern .= $this->valuesDelim;
		}

		return $pattern . '*';
	}

	/**
	 * @desc Возвращает массив масок для выбора ключей.
	 * @param Query_Abstract $query
	 * @return array
	 * 		Маски ключий для выбора.
	 */
	public function _renderSelect (Query_Abstract $query)
	{
		return $this->_compileKeyMask (
			$this->extractTable ($query),
			$query->part (Query::WHERE)
		);
	}

}