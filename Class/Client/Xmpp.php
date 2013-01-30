<?php

/**
 *
 * @desc Клиент XMPP
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Client_Xmpp extends Client_Abstract
{

	protected $_config = array (
		// Сервер, где находится демон жаббера
		'server'	=> 'localhost',
		// порт сервера
		'port'		=> 5222,

		// Хост, с которым работает жаббер
		'host'		=> 'localhost',

		// выдача логов
		'printlog'	=> false,

		// детализация логов
		'loglevel'	=> 4,

		// Данные для авторизации под админским аккаунтом,
		// используются при управлении аккаунтами (регистрации).
		'admin_username'	=> 'admin',
		'admin_password'	=> 'admin'
	);

	/**
	 *
	 * @var XMPPHP_XMPP
	 */
	protected $_xmpp;

	public function __construct ()
	{
		$this->config ();
	}

	public function config ()
	{
		if (is_array ($this->_config))
		{
			$this->_config = Config_Manager::get (
				get_class ($this),
				$this->_config
			);
		}
		return $this->_config;
	}

	/**
	 * @desc Регистрация нового пользователя
	 * @param string $username Логин вида "user@host"
	 * @param string $password Пароль
	 */
	public function registerNewUser ($username, $password)
	{
		Loader::requireOnce ('XMPPHP/XMPP.php', 'includes');
		$this->xmpp ()->registerNewUser ($username, $password);
		$this->_xmpp->disconnect ();
	}

	/**
	 * @desc
	 * @return XMPPHP_XMPP
	 */
	public function xmpp ()
	{
		if (!$this->_xmpp)
		{
			$this->_xmpp = new XMPPHP_XMPP (
				$this->_config ['host'],
				$this->_config ['port'],
				$this->_config ['admin_username'],
				$this->_config ['admin_password'],
				'xmpphp',
				$this->_config ['server'],
				$this->_config ['printlog'],
				$this->_config ['loglevel']
			);
			$this->_xmpp->connect ();
		}
		return $this->_xmpp;
	}

}
