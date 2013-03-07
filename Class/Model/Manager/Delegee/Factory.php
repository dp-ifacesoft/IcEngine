<?php

/**
 * Класс для создания моделей через фабрики
 * 
 * @author goorus, morph
 */
class Model_Manager_Delegee_Factory
{
	/**
	 * Фабрики моделей
     * 
	 * @var array
	 */
	protected static $factories;

	/**
	 * Находит фабрику модели
     * 
	 * @param Model $model Модель.
	 * @return Model_Factory Фабрика.
	 */
	public function factory($model)
	{
		$parents = class_parents($model);
		foreach ($parents as $parent) {
			if (substr ($parent, -9, 9) == '_Abstract') {
				$factory = substr($parent, 0, -9);
				if (isset (self::$factories[$factory])) {
					return self::$factories[$factory];
				}
				return self::$factories[$factory] = new $factory();
			}
		}
	}

	/**
	 * Получение данных модели
     * 
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public function get($modelName, $key, $object)
	{
		$factoryName = $modelName;
		if (!isset(self::$factories[$factoryName])) {
			self::$factories[$factoryName] = new $modelName();
		}
		$delegeeModelName = self::$factories[$factoryName]
			->delegateClass($modelName, $key, $object);
        if (!$delegeeModelName) {
            $delegeeModelName = $modelName . '_Abstract';   
        }
		$result = new $delegeeModelName(array());
		$result->setModelFactory(self::$factories[$factoryName]);
		if (is_array($object) && $object) {
			$result->set($object);
		}
		return $result;
	}
}