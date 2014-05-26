<?php
/**
 *
 * @desc Рейтинг для любой сущности
 * @author Юрий
 * @package IcEngine
 *
 */
class Component_Rating extends Model_Component
{

	/**
	 * @desc Создает и возвращает рейтинг.
	 * @param array $data Данные
	 * $data ['table'] string Модель
	 * $data ['rowId'] integer Запись
	 * @return Component_Rating
	 */
	public static function create (array $data)
	{
		return new self (array_merge (
			array (
				'value'			=> 0,
				'votes'			=> 0,
				'changeTime'	=> Helper_Date::toUnix ()
			),
			$data
		));
	}

	/**
	 * @desc Изменение рейтинга
	 * @param integer $change
	 * @return Component_Rating
	 */
	public function increment ($change)
	{
		// если гость, проверяем по сессии
		if (User::id () == 0) {
			$log = Model_Manager::byQuery (
					'Component_Rating_Log',
					Query::instance ()
						->where ('session', User_Session::getCurrent ()->phpSessionId)
						->where ('table', $this->table)
						->where ('rowId', $this->rowId)
						->order (array ('time' => Query::DESC))
			);
		} else {
			$log = Model_Manager::byQuery (
					'Component_Rating_Log',
					Query::instance ()
						->where ('User__id', User::id ())
						->where ('table', $this->table)
						->where ('rowId', $this->rowId)
						->order (array ('time' => Query::DESC))
			);
		}
		// если пользователь голосовал
		if ($log) {
			$this->update (array (
				'value'			=> $this->value + $change - $log->change,
				'votes'			=> $this->votes,
				'changeTime'	=> Helper_Date::toUnix ()
			));
		}
		else {
			$this->update (array (
				'value'			=> $this->value + $change,
				'votes'			=> $this->votes + 1,
				'changeTime'	=> Helper_Date::toUnix ()
			));
		}
		
		$log = new Component_Rating_Log (array (
			'table'		=> $this->table,
			'rowId'		=> $this->rowId,
			'change'	=> $change,
			'ip'		=> Request::ip (),
			'User__id'	=> User::id (),
			'time'		=> Helper_Date::toUnix (),
			'session'	=> User_Session::getCurrent ()->phpSessionId
		));
		$log->save();

		return $this;
	}

	/**
	 * @desc
	 * @param string $table
	 * @param string $row_id
	 * @param mixed $value
	 * @return integer
	 */
	public static function voteFor ($table, $row_id, $value)
	{
		$scheme = Model_Manager::byQuery (
			'Component_Rating_Scheme',
			Query::instance ()
				->where ('table', $table)
		);

		return $scheme->vote ($table, $row_id, $value);
	}

}