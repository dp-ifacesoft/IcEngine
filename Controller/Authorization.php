<?php
/**
 *
 * @desc Контроллер авторизации
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Authorization extends Controller_Abstract
{

	/**
	 * @desc Редирект по умолчанию после авторизация/логаута.
	 * @var string
	 */
	const DEFAULT_REDIRECT = '/';

	/**
	 * @desc Конфиг
	 * @var array
	 */
	public $_config = array (
		// Префиксы полей с формы
		'fields_prefix'				=> 'auth_',
		// Работающие авторизации
		'available'					=> 'Email_Password,Phone_Sms_Send',
		// Возможно авторизация через СМС.
		'sms_auth_enable'			=> false
	);

	/**
	 * @desc Возвращает адрес для редиректа
	 * @return string
	 */
	protected function _redirect ()
	{
		$redirect = $this->_input->receive ('redirect');
		Loader::load ('Helper_Uri');
		return Helper_Uri::validRedirect (
			$redirect ?
				$redirect :
				self::DEFAULT_REDIRECT
		);
	}

	/**
	 * @desc Досутп закрыт для текущего пользователя
	 */
	function accessDenied ()
	{
		$this->_output->send (array (
			'error' => 'access denied',
			'user'	=> User::getCurrent (),
			'data'	=> array (
				'error'	=> 'access denied'
			)
		));
	}

	/**
	 * @desc Проверка на существования пользователя с таким Email.
	 * Используется в диалоге входа/регистрации.
	 */
	public function checkEmail ()
	{
		$email = $this->_input->receive ('email');

		$exists = DDS::execute (
			Query::instance ()
			->select ('id')
			->from ('User')
			->where ('email', $email)
		)->getResult ()->asValue ();

		$this->_output->send ('data', array (
			'email'		=> $email,
			'exists'	=> (bool) $exists
		));

		$this->_task->setTemplate (null);
	}

	/**
	 * @desc Определение типа авторизации по данным формы
	 * @param string $auth_login
	 */
	public function determine ()
	{
		$login = $this->_input->receive (
			$this->config ()->fields_prefix . 'login'
		);

		$authes = explode (',', $this->config ()->available);

		/**
		 * @var Authorization_Abstract $auth
		 */
		foreach ($authes as $auth_type)
		{
			$auth = Model_Manager::byQuery (
				'Authorization',
				Query::instance ()
					->where ('name', $auth_type)
			);
			if ($auth && $auth->isValidLogin ($login))
			{
				$this->_output->send (array (
					'data'	=> array (
						'auth_type'	=> $auth_type
					)
				));
				return $this->replaceAction (
					'Authorization_' . $auth->name,
					'secondPart'
				);
			}
		}
	}

	/**
	 * @desc Авторизация.
	 * @param string login Логин.
	 * @param string password Пароль.
	 * @param string redirect [optional] Редирект после успешной авторизации.
	 */
	public function login ()
	{
		$login = $this->_input->receive ('login');

		if ($this->config ()->sms_auth_enable)
		{
			Loader::load ('Helper_Phone');
			$phone = Helper_Phone::parseMobile ($login);
			if ($phone)
			{
				return $this->replaceAction ('Authorization_Sms', 'login');
			}
		}

		$password = $this->_input->receive ('password');
		Loader::load ('Authorization');

		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('email', $login)
				->where ('password', $password)
				->where ('md5(`password`)=md5(?)', $password)
		);
		//Authorization::authorize ($login, $password);

		if (!$user)
		{
			$this->_sendError (
				'Password incorrect',
				__METHOD__,
				'/password_incorrect'
			);
			return ;
		}
		$user->authorize ();

		$this->_output->send ('data', array (
			'user'	=> array (
				'id'	=> $user->id,
				'name'	=> $user->name
			),
			'redirect'	=> $this->_redirect ()
		));
	}

	/**
	 * @desc Авторизация или регистрация.
	 */
	public function loginOrReg ()
	{
		$login = $this->_input->receive ('login');

		$login_exists = DDS::execute (
			Query::instance ()
			->select ('id')
			->from ('User')
			->where ('email', $login)
		)->getResult ()->asValue ();

		if ($login_exists)
		{
			// Авторизация
			return $this->replaceAction ($this, 'login');
		}

		// Регистрация
		return $this->replaceAction ('Registration', 'postForm');
	}

	/**
	 * Выход
	 */
	public function logout()
	{
		$this->_task->setTemplate(null);
		User::getCurrent()->logout();
		User_Session::getCurrent()->delete();
		$redirect = $this->_input->receive('redirect');
		if (!$redirect) {
			$redirect = Request::referer();
		}
		Loader::load('Helper_Uri');
		$redirect = Helper_Uri::validRedirect(
			$redirect ? $redirect : self::DEFAULT_REDIRECT
		);
		$this->_output->send('data', array(
			'redirect'	=> $redirect
		));
	}

	/**
	 * Базовая авторизация - нажата кнопка авторизации.
	 */
	public function submit()
	{
		$type = $this->_input->receive('type');
		$this->replaceAction(
			'Authorization_' . $type,
			'authorize'
		);
	}

}