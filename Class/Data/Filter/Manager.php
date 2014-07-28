<?php

/**
 * Менеджер фильтров
 *
 * @author gooorus, morph
 * @Service("dataFilterManager")
 */
class Data_Filter_Manager extends Manager_Abstract
{
	/**
	 * Подключенные фильтры
	 *
     * @var array <Filter_Abstract>
	 */
	protected $filters = array();

	/**
     * Получить фильтр по имени
     *
	 * @param string $name Фильтр.
	 * @return Filter_Abstract
	 */
	public function get($name)
	{
		if (isset($this->filters[$name])) {
			return $this->filters[$name];
		}
		$className = 'Data_Filter_' . $name;
        $filter = new $className;
		$this->filters[$name] = $filter;
        return $filter;
	}

	/**
	 * Фильтрация
	 *
     * @param string $name Фильтр
	 * @param mixed $data
	 * @return mixed
	 */
	public function filter($name, $data)
	{
		return $this->get($name)->filter($data);
	}

	/**
	 * Фильтрация с использованием схемы
	 *
     * @param string $name Фильтр
	 * @param string $field
	 * @param stdClass $data
	 * @param stdClass|Objective $scheme
	 * @return mixed
	 */
	public function filterEx($name, $field, $data, $scheme)
	{
		return $this->get($name)->filterEx($field, $data, $scheme);
	}
}