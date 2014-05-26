<?php

class Helper_Subscribe
{
	/**
	 * @desc Получить подписчика по id рассылки и email
	 * @param string $email
	 * @param integer $subscribe_id
	 * @return Model
	 */
	public static function subscribe ($email, $subscribe_id)
	{
		$subscribe = Model_Manager::byKey ('Subscribe', $subscribe_id);

		if (!$subscribe)
		{
			return;
		}

		$subscriber = Subscribe_Subscriber::byContact ($email);

		if (!$subscriber->key ())
		{
			return;
		}

		if (!$subscriber->subscribed ($subscribe))
        {
            $subscribe->sendSubscribeConfirmation ($subscriber);
        }

        return $subscriber;
	}

	public static function unsubscribe ($email, $subscribe_id)
	{
		$subscribe = Model_Manager::byKey ('Subscribe', $subscribe_id);

		if (!$subscribe)
		{
			return;
		}

		$subscriber = Subscribe_Subscriber::byContact ($email, false);

		if (!$subscriber)
		{
			return;
		}

		if ($subscriber->subscribed ($subscribe))
        {
            $subscribe->sendUnsubscribeConfirmation ($subscriber);
        }
	}
}