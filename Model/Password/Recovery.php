<?php
/**
 *
 * @desc Модель востановления пароля.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Password_Recovery extends Model
{

	/**
	 * Поле сессии
	 * @var string
	 */
	const SF_PRECOVERY = 'PASSREC';

	/**
	 * Действие
	 * Password Recovery Action
	 */
	const ACTION_SEND_CODE			= 'send_code';		 // запрос кода на емейл
	const ACTION_PASSWORD_CHANGE	= 'password_change'; // емейл проверен, меняем пароль

	/**
	 * результат восстановления пароля
	 * Password Recovery State
	 */
	const STATE_NONE					= 0; // даже не пытался
	const STATE_CODE_SENDED				= 1; // код отправлен
	const STATE_CODE_OK					= 3; // правильный код, можно менять пароль
	const STATE_PASSWORD_CHANGED		= 4; // пароль успешно изменен
	const STATE_CODE_FAIL				= 11; // неверный код восстановления
	const STATE_CODE_TIMEOUT			= 12; // истекло время действия кода
	const STATE_EMAIL_QUERY_LIMIT		= 13; // превышено кол-во запросов на емейл
	const STATE_IP_QUERY_LIMIT			= 14; // превышено кол-во запросов на ip
	const STATE_EMAIL_INCORRECT			= 15; // указан неверный e-mail
	const STATE_CODE_SEND_ERROR			= 16; // не удалось отправить e-mail
	const STATE_NEW_PASSWORD_BAD		= 17; // плохой новый пароль
	const STATE_CONFIRM_BAD				= 18; // подтверждение != паролю

	/**
	 * Максимальное количество запросов восстановления пароля в сутки
	 * @var integer
	 */
	const MAX_QUERY_PER_EMAIL	= 3;
	const MAX_QUERY_PER_IP		= 6;

	/**
	 * Ссылка для изменения пароля
	 * @var string
	 */
	public static $code;

	/**
	 *
	 * @param string $code
	 * @return Password_Recovery
	 */
	public static function byCode ($code)
	{
	    return Model_Manager::byQuery (
	        __CLASS__,
	        Query::instance ()
	        ->where ('code', $code)
	    );
	}

	/**
	 * @return Password_Recovery
	 */
	public static function fromSession ()
	{
	    if (!isset (
	        $_SESSION [self::SF_PRECOVERY],
	        $_SESSION [self::SF_PRECOVERY]['code']
	    ))
	    {
	        return null;
	    }

	    return self::byCode ($_SESSION [self::SF_PRECOVERY]['code']);
	}

	/**
	 * @param integer $id id восстановления
	 * @return string
	 */
	public static function generateUniqueCode ($id)
	{
		return
			chr (rand (ord ('a'), ord ('z'))) . $id . 'a' .
			md5 (time ()) . md5 (rand (12345678, 87654321));
	}

	/**
	 * Ссылка для изменения пароля
	 * @return string
	 */
	public function href ()
	{
		return
			"http://" . Request::host () .
			"/recovery?code=" . $this->code;
	}

	/**
	 * Количество запросов на емейл за сегодня
	 * @param string $email
	 * @return integer
	 */
	public static function queryCountOnEmail ($email)
	{
		$day = Helper_Date::eraDayNum ();

		return Model_Collection_Manager::byQuery (
		    __CLASS__,
		    Query::instance ()
		    ->where ('day', $day)
		    ->where ('email', $email)
		)->count ();
	}

	/**
	 * Количество запросов с одного IP
	 * @param string $ip
	 * @return int
	 */
	public static function queryCountOnIp ($ip)
	{
		$day = Helper_Date::eraDayNum ();

		return Model_Collection_Manager::byQuery (
		    __CLASS__,
		    Query::instance ()
		    ->where ('day', $day)
		    ->where ('ip', $ip)
		)->count();
	}

	/**
	 * @return integer
	 */
	public static function getState ()
	{
		self::$code = Request::get ('code');
		if (empty (self::$code))
		{
			self::$code = Request::post ('code');
		}

		$action = Request::get ('action');
		if (empty (self::$code))
		{
			// не передан код из письма

			// проверяем лимит запросов по IP
			if (self::queryCountOnIp (Request::ip ()) >= self::MAX_QUERY_PER_IP)
			{
				return self::STATE_IP_QUERY_LIMIT;
			}

			if ($action == self::ACTION_SEND_CODE)
			{
				$email = Request::get ('pr_email');

				// лимит запросов на e-mail
				if (self::queryCountOnEmail ($email) >= self::MAX_QUERY_PER_EMAIL)
				{
					return self::STATE_EMAIL_QUERY_LIMIT;
				}

				$user = Model_Manager::byQuery (
				    'User',
				    Query::instance ()
				    ->where ('email', $email)
				);

				if (!$user)
				{
					return self::STATE_EMAIL_INCORRECT;
				}

				// Всё правильно, создаем письмо с кодом

				if (self::sendRecoveryEmail ($user->id, $email))
				{
					return self::STATE_CODE_SENDED;
				}
				else
				{
					return self::STATE_CODE_SEND_ERROR;
				}

			}
		}
		else
		{
			// передан код из письма
			$recovery = self::byCode (self::$code);

			// Код не соответсвует id запроса
			if (!$recovery)
			{
				return self::STATE_CODE_FAIL;
			}

			// Истек срок действия кода
			if ($recovery->active == 0)
			{
				return self::STATE_CODE_FAIL;
			}

			if ($action == self::ACTION_PASSWORD_CHANGE)
			{
				$new_password = Request::post ('pr_newpassword');
				$confirm = Request::post ('pr_confirm');

				if (strcmp ($new_password, $confirm) != 0)
				{
					return self::STATE_CONFIRM_BAD;
				}

				if (strlen ($new_password) < 3)
				{
					return self::STATE_NEW_PASSWORD_BAD;
				}

				// меняем пароль и делаем неактивной смену
				$recovery->update (array (
					'active'	=> 0
				));
				$recovery->User->update (array (
					'password'	=> $new_password
				));
				return self::STATE_PASSWORD_CHANGED;
			}
			else
			{
				$_SESSION [self::SF_PRECOVERY] = array(
					'state_id'	=> self::STATE_CODE_OK,
					'code'	    => self::$code
				);
				return self::STATE_CODE_OK;
			}

		}

		return self::STATE_NONE;
	}

	/**
	 * Удаление старых
	 * Дает возможность заного запрашивать восстановления пароля
	 * на использованных IP и мыле
	 */
	public static function processOld ()
	{
		$from = 1;
		$to = 7;
		$sec_in_day = 24 * 60 * 60;
		for ($i = $from; $i < $to; ++$i)
		{
			$day = Helper_Date::eraDayNum (time () - $i * $sec_in_day);
			$recoverys = Model_Collection_Manager::byQuery (
			    __CLASS__,
			    Query::instance ()
			    	->where ('day', $day)
			);
		}
	}

	public static function resetSession ()
	{
	    $_SESSION [self::SF_PRECOVERY] = array (
			'state_id'	=> self::STATE_NONE);
	}

	/**
	 * @param integer $user_id
	 * @param string $email
	 * @return Password_Recovery
	 */
	public static function sendRecoveryEmail ($user_id, $email)
	{
		$code = self::generateUniqueCode (time ());

		$recovery = new Password_Recovery (array (
			'User__id'		=> $user_id,
			'email'			=> $email,
			'queryTime'	    => date ('Y-m-d H:i:s'),
			'ip'			=> Request::ip (),
			'code'			=> $code,
			'active'		=> 1,
		    'day'			=> Helper_Date::eraDayNum ()
		));
		$recovery->save ();
		
		$message = Mail_Message::create (
			'password_recovery',
			$email, $email,
			array (
				'email'		=> $email,
				'time'		=> $recovery->queryTime,
				'code'		=> $recovery->code,
				'href'		=> $recovery->href ()
			),
			$user_id,
			'Mimemail'
		);
		return $message->send ();
	}

	public function startSession ()
	{
	    $_SESSION [self::SF_PRECOVERY] = array (
			'state_id'	=> self::STATE_CODE_OK,
			'code'		=> $this->code);
	}

}