<?php

/**
 * Модель, необходимая для организации фабрик.
 * Используется в случаях, когда модели могут быть реализованы
 * разными классами.
 *
 * @author goorus, morph
 */
class Model_Factory extends Model
{
	/**
	 * Возвращает название класса, который будет использоваться
	 * в качестве модели.
     *
	 * @param string $modelName Название модели.
	 * @param string $key Первичный ключ.
	 * @param array $source Имеющиеся данные об объекте.
	 * @return string Название класса модели.
	 */
	public function delegateClass($modelName, $key, $source)
	{
	    if (is_array($source) && isset($source['name'])) {
		    return $modelName . '_' . $source['name'];
	    }
	}

	/**
	 * @inheritdoc
	 */
	public function table()
	{
		return get_class($this);
	}
}