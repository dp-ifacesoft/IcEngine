<?php
/**
 *
 * @desc Модель, необходимая для организации фабрик.
 * Используется в случаях, когда модели могут быть реализованы
 * разными классами.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Factory extends Model
{

	/**
	 * @desc Возвращает название класса, который будет использоваться
	 * в качестве модели.
	 * @param string $model Название модели.
	 * @param string $key Первичный ключ.
	 * @param array $object Имеющиеся данные об объекте.
	 * @return string Название класса модели.
	 */
	public function delegateClass ($model, $key, $object)
	{
	    if (is_array ($object) && isset ($object ['name']))
	    {
		    return $model . '_' . $object ['name'];
	    }
		$query =  Query::instance ()
			->select ('name')
			->from ($this->table())
			->where ('id', $key);
	    $delegateName = DDS::execute ($query)->getResult ()->asValue();
		$delegateName = $delegateName ?: 'Abstract';
		return $model . '_' . $delegateName;
	}

	/**
	 * @desc Возвращает таблицу
	 * @return string
	 */
	public function table ()
	{
		return get_class($this);
	}

}