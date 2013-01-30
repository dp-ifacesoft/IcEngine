<?php

class Subscribe_Subscriber extends Model
{

    /**
     * @desc Получить подписчика по email
     * @param string $email
     * @param boolen $autocreate
     * @return Subscriber_Subscriber
     */
    public static function byContact ($email, $autocreate = true)
    {
        $subscriber = Model_Manager::byQuery (
            'Subscribe_Subscriber',
            Query::instance ()
            ->where ('contact', $email)
        );

        if (!$subscriber)
        {
			if ($autocreate)
			{
				$subscriber = new Subscribe_Subscriber (array (
					'active'		=> 1,
					'date'			=> Helper_Date::toUnix (),
					'contact'		=> $email
				));
				$subscriber->save ();
			}
        }
        else
        {
        	$subscriber->data ('exists', 1);
        }

        return $subscriber;
    }

    /**
     *
     * @param Subscribe_Abstract|integer $subscribe
     * @return boolean
     */
    public function subscribed ($subscribe)
    {
        if (!($subscribe instanceof Subscribe_Abstract))
        {
            $subscribe = Model_Manager::get ('Subscribe', (int) $subscribe);
        }
        $join = $subscribe->subscriberJoin ($this);

        return $join ? (bool) $join->active : false;
    }

    /**
     * Возвращает время в секундах с последнего запроса.
     * @return integer
     */
    public function timeLeft ()
    {
        return time - Helper_Date::strToTimestamp ($this->codeSendTime);
    }

}