<?php

class Subscribe_Session extends Model
{
	/**
	 * @desc Получить последнюю сессию подписки
	 * @param Model $subscibe
	 * @return array
	 */
	public static function lastFor (Model $subscribe)
	{
		return DDS::execute (
			Query::instance ()
			->select ('*')
			->from ('Subscribe_Session')
			->where ('Subscribe__id', $subscribe->key ())
			->where ('status', Helper_Process::SUCCESS)
			->order ('finishDate DESC')
			->limit (1)
		)
			->getResult ()
				->asRow ();
	}

	/**
	 * @desc Получить успешную последнюю сессию подписки
	 * для подписчика
	 * @param Model $subscribe
	 * @param Model $subsciber
	 * @return array
	 */
	public static function lastForSubscriber (Model $subscribe, Model $subscriber)
	{
		return DDS::execute (
			Query::instance ()
			->select ('*')
			->from ('Subscribe_Session')
			->innerJoin (
				'Subscribe_Subscriber_Attribute',
				'Subscribe_Session.id=Subscribe_Subscriber_Attribute.value'
			)
			->where ('Subscribe__id', $subscribe->key ())
			->where ('status', Helper_Process::SUCCESS)
			->where ('Subscribe_Subscriber__id', $subscriber->key ())
			->where ('key', 'SubscribeTour_session_id')
			->order ('finishDate DESC')
			->limit (1)
		)
			->getResult ()
				->asRow ();
	}

	/**
	 * @desc Изменить статус сессии
	 * @param integer $status
	 * @return Model
	 */
	public function setStatus ($status)
	{
		return $this->update (array (
			'status'	=> $status
		));
	}
}