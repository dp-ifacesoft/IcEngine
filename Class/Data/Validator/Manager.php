<?php

/**
 * Менеджер валидаторов данных
 * 
 * @author morph
 * @Service("dataValidatorManager")
 */
class Data_Validator_Manager extends Manager_Abstract
{
	/**
	 * Валидаторы
     * 
	 * @var array <Data_Validator_Abstract>
	 */
	private $validators = array();

	/**
	 * Получить валидатор по имени
     * 
	 * @param string $name
	 * @return Data_Validator_Abstract
	 */
	public function get($name)
	{
		if (isset ($this->validators[$name])) {
			return $this->validators[$name];
		}
		$className = 'Data_Validator_' . $name;
		$validator = new $className;
        $this->validators[$name] = $validator;
        return $validator;
	}
}