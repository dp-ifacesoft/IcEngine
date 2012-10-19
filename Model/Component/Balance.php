<?php
/**
 *
 * @desc Модель баланса.
 * @author Гурус
 * @package IcEngine
 *
 */
class Component_Balance extends Model_Component
{

	/**
	 * @desc Получение баланса для модели.
	 * @param Model $model Модель.
	 * @return User_Balance Баланс.
	 */
	public static function getFor ($model)
	{
		return $model->component ('Balance', 0);
	}

	/**
	 * @desc Изменяет баланс модели.
	 * @param Model $model Модель.
	 * @param float $change Изменение баланса.
	 * @param string $comment Комментарий.
	 * @return Component_Balance Баланс модели.
	 */
	public static function changeFor ($model, $change, $comment = '')
	{
		$balance = self::getFor ($model);

		$balance->change ($change, $comment);

		return $balance;
	}

	/**
	 * @desc Изменяет баланс модели
	 * @param float $change
	 * @param string $comment [optional]
	 * @return Component_Balance_Log
	 */
	public function change ($change, $comment = '')
	{
		$log = Component_Balance_Log::addLog (
			$this->table,
			$this->rowId,
			$this->value,
			$change,
			$comment
		);

		$this->update (array (
			'value'	=> $this->value + $change
		));

		return $log;
	}

}