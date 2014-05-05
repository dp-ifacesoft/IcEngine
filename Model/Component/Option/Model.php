<?php
/**
 *
 * @desc Опция для выбора компонент по модели.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Component_Option_Model extends Model_Option
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 * @param string $table Название таблицы.
	 * @param string $row_id Первичный ключ модели.
	 * @param Model $model Вместо названия таблицы и ПК может быть передана
	 * сама модель.
	 */
	public function before ()
	{
		if (isset ($this->params ['model']))
		{
			$this->query
				->where ('table', $this->params ['model']->modelName ())
				->where ('rowId', $this->params ['model']->key ());
		}
		else
		{
			$this->query
				->where ('table', $this->params ['table'])
				->where ('rowId', $this->params ['row_id']);
		}
	}

}