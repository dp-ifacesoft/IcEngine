<?php

/**
 * Класс для создания моделей через фабрики
 *
 * @author goorus, morph
 */
class Model_Manager_Delegee_Factory extends Model_Manager_Delegee_Abstract
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
        array_unshift($parents, get_class($model));
		foreach ($parents as $parent) {
			if (substr($parent, -9, 9) != '_Abstract') {
                continue;
            }
            $factory = substr($parent, 0, -9);
            if (isset(self::$factories[$factory])) {
                return self::$factories[$factory];
            }
            self::$factories[$factory] = new $factory();
            return self::$factories[$factory];
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
	public function get($modelName, $key, $object = null)
	{
		$factoryName = $modelName;
		if (!isset(self::$factories[$factoryName])) {
			self::$factories[$factoryName] = new $factoryName();
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