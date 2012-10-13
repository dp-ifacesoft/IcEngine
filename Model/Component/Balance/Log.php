<?php
/**
 * 
 * @desc Модель логов баланса
 * @author Гурус
 * @package IcEngine
 *
 */
class Component_Balance_Log extends Model
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
	public static function addLog ($table, $row_id, $value, $change, $comment = '')
	{
		$log = new Component_Balance_Log (array (
			'time'			=> Helper_Date::toUnix (),
			'table'			=> $table,
			'rowId'			=> $row_id,
			'change'		=> $change,
			'comment'		=> $comment
		));
		return $log->save ();
	}
	
}