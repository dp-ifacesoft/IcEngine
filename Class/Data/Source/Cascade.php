<?php
/**
 * 
 * @desc Абстрактный класс сорса
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Data_Source_Cascade extends Data_Source_Abstract
{
	
	/**
	 * 
	 * @var array <Data_Mapper_Abstract>
	 */
	protected $_mappers = array ();
	
	/**
	 * @desc Вычисление ключа запроса.
	 * @param Query $query Запрос.
	 * @return string Ключ.
	 */
	protected function _queryKey (Query $query)
	{
		return md5 (json_encode ($query->parts ()));
	}
	
	public function addDataMapper (Data_Mapper_Abstract $mapper)
	{
		$this->_mappers [] = $mapper;
		return $this;
	}
	
	/**
	 * @desc Проверяет доступность источника данных
	 * @return boolean
	 */
	public function available ()
	{
		return (bool) count ($this->_mappers);
	}
	
	/**
	 * 
	 * @param Query $query
	 * @param Query_Options $options
	 * @return Data_Source_Abstract $this
	 */
	public function execute ($query = null, $options = null)
	{
		$this->setQuery ($query);
		$key = $this->_queryKey ($query);
		
		foreach ($this->_mappers as $index => $mapper)
		{
			$this->_mapper = $mapper;
			$this->setResult ($mapper->execute ($this, $query, $options));
			
			if ($query->type () == Query::SELECT)
			{
				if (
					$this->success () || 
					$index == count ($this->_mappers) - 1
				)
				{
					for ($i = 0; $i < $index; ++$i)
					{
						$this->_mappers [$i]->unlock (
							$key,
							$query,
							$options, 
							$this->_result
						);
					}
					return $this;
				}
				else
				{
					$this->_mapper->lock ($key);
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * 
	 * @param Data_Mapper_Abstract $mapper
	 */
	public function setDataMapper (Data_Mapper_Abstract $mapper)
	{
		$this->_mapper = $mapper;
		$this->_mappers [] = $mapper;
		return $this;
	}
	
}
