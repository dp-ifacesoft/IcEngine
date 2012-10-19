<?php

/**
 *
 * Коллекция изображений к сущностям.
 * @author Юрий
 *
 */

class Component_Image_Collection extends Component_Collection
{
	/**
	 * Загрузка списка комментариев для записи
	 *
	 * @param Model $model
	 * 		Модель, для которой подгружаются объекты
	 * @return Component_Collection
	 * 		Экземпляр коллекции
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;

		$this
		    ->where ('table', $this->_model->table ())
		    ->where ('rowId', $this->_model->key ());

		return $this;
	}

}