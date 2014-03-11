<?php
/**
 *
 * @desc Мэппер для моделей, данные которых перечисленны в самом классе.
 * @author Илья Колесников
 * @package IcEngine
 *
 */
class Data_Mapper_Defined extends Data_Mapper_Abstract
{
	/**
	 * @desc Запрос
	 * @var array
	 */
	protected $_where;

    /**
     * @desc
     * @param array $row
     * @internal param array $data
     * @internal param array $filter
     * @return array
     */
	public function filter (array $row)
	{
        $valid = true;

		foreach ($this->_where as $where)
		{
			$field = $where [Query::WHERE];
			$value = $where [Query::VALUE];

			$field = str_replace (' ', '', $field);

			$s = substr ($field, -2, 2);
			$offset = 2;

			if (ctype_alnum ($s))
			{
				$s = '=';
				$offset = 0;
			}

			elseif (ctype_alnum ($s [0]))
			{
				$s = $s [1];
				$offset = 1;
			}

			if ($offset)
			{
				$field = substr ($field, 0, -1 * $offset);
			}

            $pos = strpos($field, '.');

            $field = substr($field, $pos === false ? 0 : $pos + 1);

			switch ($s)
			{
				case '=':
					$valid = $row [$field] == $value;
					break;
				case '>':
					$valid = $row [$field] > $value;
					break;
				case '>=':
					$valid = $row [$field] >= $value;
					break;
				case '<': $valid = $row [$field] < $value;
					break;
				case '<=': $valid = $row [$field] <= $value;
					break;
				case '!=': $valid = $row [$field] != $value;
					break;
			}
		}

		return $valid;
	}

	/**
	 * @desc
	 * @param Query $query
	 */
	protected function _selectQuery (Data_Source_Abstract $source,
		Query_Abstract $query)
	{
		$select = $query->getPart (Query::SELECT);

		$model_name = reset ($select);
		if (is_array ($model_name))
		{
			$model_name = reset ($model_name);
		}
		else
		{
			$from = $query->getPart(Query::FROM);
			$model_name = key($from);
		}
		$result = $model_name::$rows;
		$this->_where = $query->getPart (Query::WHERE);
		if ($this->_where) {
			$result = array_filter (
				$model_name::$rows,
				array ($this, 'filter')
			);
		}
		$found_rows = count ($result);
		return new Query_Result (array (
			'error'			=> '',
			'errno'			=> 0,
			'query'			=> $query,
			'startAt'		=> 0,
			'finishedAt'	=> 0,
			'foundRows'		=> $found_rows,
			'result'		=> array_values ($result),
			'touchedRows'	=> 0,
			'insertKey'		=> 0,
			'currency'		=> 1,
			'source'		=> $source
		));
	}

	protected function _showQuery (Data_Source_Abstract $source, Query_Abstract $query)
	{
	}

	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::execute()
	 */
	public function execute (Data_Source_Abstract $source, Query_Abstract $query, $options = null)
	{
		$method = strtolower ($query->type ());

		return call_user_func (
			array ($this, '_' . $method . 'Query'),
			$source, $query
		);
	}
}