<?php

/**
 * Менеджер драйверов для источников данных
 * 
 * @author goorus, morph
 * @Service("dataDriverManager")
 */
class Data_Driver_Manager extends Manager_Abstract
{
	/**
	 * Уже созданные драйверы
     * 
	 * @var array
	 */
	protected $drivers = array();

	/**
	 * Получить драйвер по имени
     * 
	 * @param string $name
     * @param array $params
	 * @return Data_Driver_Abstract
	 */
	public function get($name, $params = array())
	{
		$className = 'Data_Driver_' . $name;
		$driver = new $className;
        if ($params) {
            foreach ($params as $key => $value) {
                $driver->setOption($key, $value);
            }
        }
		return $driver;
	}
}