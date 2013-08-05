<?php

/**
 * Модель компонента.
 * Компоненты - модели, крепящиеся к другим моделям.
 * Коллекция компонентов может быть получена от модели через метод
 * component ().
 *
 * @author goorus
 */
abstract class Model_Component extends Model
{

    /**
     * Переподключение компонента к другой модели
	 *
     * @param Model $model Модель, к которой будет подключен компонент.
     * @return Model_Component Этот компонент.
     */
    public function rejoin(Model $model)
    {
    	if ($model) {
    		$this->update(array(
	            'table'	=> $model->table(),
	            'rowId'	=> $model->key()
	        ));
    	} else {
	        $this->update(array(
	            'table'	=> '',
	            'rowId'	=> 0
	        ));
    	}
        return $this;
    }

	/**
	 * Модель, к которой привязан компонент в данный момент.
	 *
	 * @return Model
	 */
    public function model()
    {
    	return $this->getService('modelManager')->byKey(
			$this->table,
			$this->rowId
		);
    }
}