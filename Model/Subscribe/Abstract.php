<?php
/**
 *
 * @desc Абстрактная модель рассылки
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Subscribe_Abstract extends Model_Factory_Delegate
{

	protected static $_config = array (
		// Шаблон письма на подтверждение рассылки
		'confirm_subscribe_template'	=> 'subscribe_activate',
		// Шаблон письма на подтверждение отказа
		'confirm_unsubscribe_template'	=> 'subscribe_deactivate',
		// Провайдер сообщений
		'mail_provider'					=> 'Mimemail'
	);

	/**
	 *
	 * @param stirng $code
	 * @return string
	 */
	protected function _confirmSubscribeHref ($code)
	{
	    return 'http://www.vipgeo.ru/subscribe/activate/' . $code;
	}

	/**
	 *
	 * @param string $code
	 * @return string
	 */
	protected function _confirmUnsubscribeHref ($code)
	{
	    return 'http://www.vipgeo.ru/subscribe/deactivate/' . $code;
	}

	/**
	 * @desc Создает сессию рассылки. Готовит статусы для отправки
	 * @param Model_Collection $subscribers
	 * @param null|Mail_Template $mail_template
	 * @param string $comment
	 * @return Model
	 */
	public function createSession (Model_Collection $subscribers,
		$mail_template = null, $comment = '')
	{
		$session = new Subscribe_Session (array (
			'Subscribe__id'			=> $this->key (),
			'beginDate'				=> Helper_Date::toUnix (),
			'finishDate'			=> Helper_Date::NULL_DATE,
			'status'				=> Helper_Process::NONE,
			'comment'				=> $comment,
			'Mail_Template__id'		=> $mail_template
				? $mail_template->key ()
				: 0
		));

		$session->save ();

		foreach ($subscribers as $subscriber)
		{
			$status = new Subscribe_Subscriber_Status (array (
				'Subscribe_Subscriber__id'		=> $subscriber->key (),
				'Subscribe__id'					=> $this->key (),
				'Subscribe_Session__id'			=> $session->key (),
				'status'						=> Helper_Process::NONE
			));
			$status->save ();
		}

		return $session;
	}

	/**
	 *
	 * @param Subscribe_Subscriber $subscriber
	 */
	public function sendSubscribeConfirmation (
	    Subscribe_Subscriber $subscriber)
	{
	    $join = self::subscriberJoin ($subscriber, true)->regenCode ();

	    $mail = Mail_Message::create (
	    	$this->config ()->confirm_subscribe_template,
	        $subscriber->contact,
	        '',
	        array (
	            'code'          => $join->code,
	            'subscribe'	    => $this,
	            'subscriber'	=> $subscriber,
	            'href'		    => $this->_confirmSubscribeHref ($join->code)
	        ),
	        User::id (),
	        $this->config ()->mail_provider
	    );

	    $mail->send ();
	}

	/**
	 *
	 * @param Subscribe_Subscriber $subscriber
	 */
	public function sendUnsubscribeConfirmation (
	    Subscribe_Subscriber $subscriber)
	{
	    $join = self::subscriberJoin ($subscriber, true)->regenCode ();

	    $mail = Mail_Message::create (
	    	$this->config ()->confirm_unsubscribe_template,
	        $subscriber->contact,
	        '',
	        array (
	            'code'          => $join->code,
	            'subscribe'	    => $this,
	            'subscriber'	=> $subscriber,
	            'href'		    => $this->_confirmUnsubscribeHref ($join->code)
	        ),
	        User::id (),
	        $this->config ()->mail_provider
	    );

	    $mail->send ();
	}

	/**
	 *
	 * @param Background_Process $daemon
	 * @return Abstract
	 */
	public function setDaemon (Background_Process $daemon)
	{
		$this->_daemon = $daemon;
		return $this;
	}

	/**
	 *
	 * @param Subscribe_Provider_Abstract $provider
	 * @return Abstract
	 */
	public function setProvider (Subscribe_Provider_Abstract $provider)
	{
		$this->_provider = $provider;
		return $this;
	}

	/**
	 *
	 * @param Subscribe_Render_Abstract $render
	 * @return Abstract
	 */
	public function setRender (Subscribe_Render_Abstract $render)
	{
		$this->_render = $render;
		return $this;
	}

	/**
	 *
	 * @param Subscribe_Subscriber $subscriber
	 * @param boolean $autocreate
	 * @return Subscribe_Subscriber_Join|null
	 */
	public function subscriberJoin (Subscribe_Subscriber $subscriber,
	    $autocreate = false)
	{
	    $join = Model_Manager::byQuery (
	        'Subscribe_Subscriber_Join',
	        Query::instance ()
		        ->where ('Subscribe__id', $this->id)
		        ->where ('Subscribe_Subscriber__id', $subscriber->id)
	    );

	    if (!$join && $autocreate)
	    {
	        $join = new Subscribe_Subscriber_Join (array (
	            'Subscribe__id'               => $this->id,
	            'Subscribe_Subscriber__id'    => $subscriber->id,
	            'active'					  => 0,
	            'fromDate'				      => date ('Y-m-d H:i:s'),
	            'code'						  => ''
	        ));
	        $join->save ();
	    }
	    
	    return $join;
	}

}
