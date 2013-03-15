<?php
/**
 * Менеджер дата мэперов
 * 
 * @author goorus, morph
 * @Service("dataMapperManager")
 */
class Data_Mapper_Manager extends Manager_Abstract
{
	/**
	 * Уже созданные мэперы
     * 
	 * @var array
	 */
	protected $mappers = array();

	/**
	 * Получить мэппер по имени
     * 
	 * @param string $name
     * @param array $params
	 * @return Data_Mapper_Abstract
	 */
	public function get($name, $params = array())
	{
		if (!isset($this->mappers[$name])) {
			$className = 'Data_Mapper_' . $name;
			$this->mappers[$name] = new $className;
		}
        if ($params) {
            foreach ($params as $key => $value) {
                $this->mappers[$name]->setOption($key, $value);
            }
        }
		return $this->mappers[$name];
	}
}