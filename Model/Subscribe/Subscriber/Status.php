<?php

class Subscribe_Subscriber_Status extends Model
{
	/**
	 * @desc Получить статусы для сессия
	 * @param integer $session_id
	 * @param integer $status
	 * @return Model_Collection
	 */
	public static function forSession ($session_id, $status, $limit = null)
	{
		$query = Query::instance ()
				->select ('*')
				->from ('Subscribe_Subscriber_Status')
				->where ('status', $status)
				->where ('Subscribe_Session__id', $session_id)
				->limit ($limit);

		$collection = new Subscribe_Subscriber_Status_Collection;

		return $collection->fromQuery ($query);
	}

	/**
	 * @desc Изменить статус
	 * @param integer $status
	 * @return Model
	 */
	public function setStatus ($status)
	{
		$this->status = $status;
		$this->save ();
		return $this;
	}
}