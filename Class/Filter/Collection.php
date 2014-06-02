<?php
/**
 * 
 * @desc Коллекция фильтров
 * @author Илья
 * @package IcEngine
 *
 */
class Filter_Collection 
{
	
	/**
	 * @desc Фильтры
	 * @var array <Filter_Abstract>
	 */
	protected $_filters;
	
	/**
	 * @desc Возвращает экземпляр коллекции фильтров.
	 */
	public function __construct ()
	{
		$this->_filters = array ();
	}
	
	/**
	 * @desc Добавляет фильтр в коллекцию.
	 * @param Filter_Abstract $filter
	 */
	public function append ($filter)
	{
		$this->_filters [] = $filter;
		return $this;
	}
	
	/**
	 * @desc Последовательно применяет фильтры на данные.
	 * @param mixed $data
	 * @param array|null $fields
	 * @return Filter_Collection
	 */
	public function apply (&$data, $fields = null)
	{
		if (!is_null ($fields))
		{
			$fields = (array) $fields;
		}
		
		if (!is_array ($data))
		{
			for ($i = 0, $count = sizeof ($this->_filters); $i < $count; $i++)
			{
				$data = $this->_filters [$i]->filter ($data);
			}
		}
		else
		{
			foreach ($data as $key=>&$value)
			{
				if ($fields && !in_array ($key, $fields))
				{
					continue;
				}
				for ($i = 0, $count = sizeof ($this->_filters); $i < $count; $i++)
				{
					$value = $this->_filters [$i]->filter ($value);
				}
			}
		}
		
		return $this;
	}
}