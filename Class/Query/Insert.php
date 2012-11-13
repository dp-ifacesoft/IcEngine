<?php

/**
 * Запрос типа insert
 *
 * @author morph, goorus, neon
 */
class Query_Insert extends Query_Abstract
{
	/**
	 * @see Query::_defaults
	 */
	public static $_defaults = array (
		Query::VALUES => array ()
	);

	private $multiple = false;

	/**
	 * @desc Запрос преобразуется в запрос на вставку
	 * @param string $table
	 * @return Query
	 */
	public function insert ($table)
	{
		$this->_parts [Query::INSERT] = $table;
		$this->_type = Query::INSERT;
		return $this;
	}

	/**
	 * Получить значение флага, на множественную вставку
	 *
	 * @return bool
	 */
	public function getMultiple()
	{
		return $this->multiple;
	}

	/**
	 * @see Query_Select::getTags()
	 */
	public function getTags ()
	{
		$tags = array ();

		$insert = $this->getPart (Query::INSERT);
		if ($insert)
		{
	   		$tags [] = Model_Scheme::table ($insert);
		}

		return array_unique ($tags);
	}

	/**
	 * @see Query::reset()
	 */
	public function reset ()
	{
		parent::reset ();
		$this->_type = Query::INSERT;
		return $this;
	}

	/**
	 * Помечаем запрос, как запрос на множественный INSERT
	 *
	 * @param bool $value
	 * @return void
	 */
	public function setMultiple($value)
	{
		$this->multiple = $value;
	}

	/**
	 * Установка значений для INSERT/UPDATE
	 *
	 * @param array $values
	 * @param bool $multiple отвечает за разделение () при множественном
	 *  INSERT
	 * @return Query Этот запрос.
	 */
	public function values (array $values, $multiple = false)
	{
		if ($multiple) {
			if (!$this->multiple) {
				$this->setMultiple($multiple);
			}
			$this->_parts[QUERY::VALUES][] = $values;
		} else {
			if (isset($this->_parts[Query::VALUES])) {
				$this->_parts[Query::VALUES] = array_merge(
					$this->_parts[Query::VALUES],
					$values
				);
			} else {
				$this->_parts[Query::VALUES] = $values;
			}
		}
		return $this;
	}
}