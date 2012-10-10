<?php
/**
 * 
 * @desc Модель логов баланса
 * @author Гурус
 * @package IcEngine
 *
 */
class Component_Balance_Log extends Model_Component
{
	
	/**
	 * @desc Добавить запись в лог об изменении баланса.
	 * @param string $table Таблица.
	 * @param integer $row_id Запись.
	 * @param float $value Текущее значение баланса.
	 * @param float $change Изменение баланса.
	 * @param string $comment [optional] Комментарий.
	 * @return User_Balance_Log Созданный лог.
	 */
	public static function addLog ($table, $row_id, $value, $change, $comment, $model, $service, $discount)
	{
		$log = new Component_Balance_Log (array (
			'time'			=> Helper_Date::toUnix (),
			'table'			=> $table,
			'rowId'			=> $row_id,
			'value'			=> $value,
			'change'		=> $change,
			'comment'		=> $comment,
			'model'			=> $model->modelName(),
			'model_id'		=> $model->key(),
			'service'		=> $service,
			'discount'		=> $discount
		));
		return $log->save ();
	}
	
}