<?php

/**
 * @desc Метод схемы связей модели, возвращающий пустую коллекцию моделей
 * @author Илья Колесников
 */
class Model_Mapper_Method_Collection extends Model_Mapper_Method_Abstract
{
	/**
	 * @desc Получить коллекцию
	 * @return Model
	 */
	public function get()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $collectionManager = $serviceLocator->getService('collectionManager');
		return $collectionManager->create($this->_params [0]);
	}
}