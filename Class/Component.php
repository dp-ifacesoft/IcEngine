<?php
/**
 *
 * @desc
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Component extends Model
{

	/**
	 * @desc Получение коллекции компонент указанного типа для модели.
	 * @param Model $model Модель
	 * @param string $type Тип данных
	 * @return Component_Collection Связанные данные
	 */
	public static function getFor (Model $model, $type)
	{
		$collection_class = 'Component_' . $type;

		return Model_Collection_Manager::create ($collection_class)
			->getFor ($model);
	}

}
