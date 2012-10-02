<?php

/**
 *
 * @desc Сессия пользователя
 * @author Юрий
 * @package IcEngine
 *
 */
class User_Session extends Model
{

    /**
     * @desc Сессия текущего пользователя.
     * @var User_Session
     */
    protected static $_current = null;

	/**
	 * @desc id пользователя по умолчаиню
	 * @var integer
	 */
	protected static $_defaultUserId = 0;

	/**
	 *
	 * @param string $session_id
	 * @return User_Session
	 */
	public static function byPhpSessionId ($session_id, $autocreate = true)
	{
		$session = Model_Manager::byKey ('User_Session', $session_id);

		if (!$session && $autocreate)
		{
    		$session = new User_Session (array (
    			'id'			=> $session_id,
    			'User__id'		=> self::$_defaultUserId,
    			'phpSessionId'	=> $session_id,
    			'startTime'	    => Helper_Date::toUnix (),
    			'lastActive'	=> Helper_Date::toUnix (),
    			'remoteIp'		=> Request::ip (),
				'eraHourNum'	=> Helper_Date::eraHourNum(),
    			'userAgent'	    => substr (getenv ('HTTP_USER_AGENT'), 0, 100)
    		));
    		$session->save (true);
		}

		return $session;
	}

	/**
	 * @return User_Session
	 */
	public static function getCurrent ()
	{
	    return self::$_current;
	}

	/**
	 * @desc Возвращает ПК пользователя по умолчанию.
	 * @return integer
	 */
	public static function getDefaultUserId ()
	{
		return self::$_defaultUserId;
	}

	/**
	 *
	 * @param User_Session $session
	 */
	public static function setCurrent (User_Session $session)
	{
	    self::$_current = $session;
	}

	/**
	 * @desc Устанавливает ПК пользователя по умолчанию
	 * @param integer $value ПК пользователя
	 */
	public static function setDefaultUserId ($value)
	{
		self::$_defaultUserId = $value;
	}

	/**
	 * @param integer $new_user_id [optional] Изменить пользователя.
	 * @return User_Session
	 */
	public function updateSession ($new_user_id = null)
	{
		$now = Helper_Date::toUnix ();

		if (isset ($new_user_id) && $new_user_id)
		{
			$upd = array (
				'User__id'		=> $new_user_id,
				'lastActive'	=> $now,
				'eraHourNum'	=> Helper_Date::eraHourNum()
			);
		}
		else
		{
			// Обновляем сессию не чаще, чем раз в 10 минут.
			// strlen ('YYYY-MM-DD HH:I_:__') =
			if (strncmp ($now, $this->lastActive, 15) == 0)
			{
				return $this;
			}

			$upd = array (
				'lastActive'	=> $now,
				'eraHourNum'	=> Helper_Date::eraHourNum()
			);
		}

	    $this->update ($upd);

		return $this;
	}

}