<?php
/**
 *
 * @desc Абстрактный контроллер авторизации.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Controller_Authorization_Abstract extends Controller_Abstract
{

	/**
	 * @desc Configuration
	 * @var array
	 */
	protected $_config = array (
		// Включена ли авторегистрация
		'autoreg_enable'		=> false,

		// Контроллер и экшен, куда будет перенаправлен
		// запрос для авторегистрации
		'autoreg_controller'	=> 'Registration',
		'autoreg_action'		=> 'autoregister',

		// префикс полей авторизации
		'fields_prefix'			=> 'auth_',

		// эта авторизация активна
		'enabled'				=> true
	);

	/**
	 * @desc Возвращает связанную модель авторизации.
	 * @return Authorization_Abstract
	 */
	protected function _authorization ()
	{
		$name = substr (
			get_class ($this),
			strlen ('Controller_Authorization_')
		);
		return Model_Manager::byQuery (
			'Authorization',
			Query::instance ()
			->where ('name', $name)
		);
	}

	/**
	 * @desc Авторизация
	 */
	public function authorize ()
	{
		$prefix = $this->config ()->fields_prefix;

		$data = Helper_Array::prefixed (
			$this->_input->receiveAll (),
			$prefix
		);

		$user = $this->_authorization ()->authorize ($data);

		if (
			$user == 'Data_Validator_Authorization/userNotFound' &&
			$this->config ()->autoreg_enable
		)
		{
			return $this->replaceAction (
				$this->config ()->autoreg_controller,
				$this->config ()->autoreg_action
			);
		}

		if (!is_object ($user))
		{
			// Неудачная авторизация
			if ($user)
			{
				$this->_sendError (
					$user,
					$user
				);
			}
			else
			{
				$this->_sendError (
					'fail',
					get_class ($this) . '::authorize',
					'/fail'
				);
			}

			return ;
		}

		// успешная авторизация
		$redirect = $this->_input->receive ('redirect');
		$redirect = Helper_Uri::validRedirect ($redirect);

		$this->_output->send (array (
			'redirect'	=> $redirect,
			'data'		=> array (
				'redirect'	=> $redirect
			)
		));
	}

	/**
	 * @desc Вторая часть диалога авторизации
	 */
	public function secondPart ()
	{
		$login = $this->_input->receive (
			$this->config ()->fields_prefix . 'login'
		);

		$this->_output->send (array (
			'login'			=> $login,
			'registered'	=> $this->_authorization ()->isRegistered ($login)
		));
	}

}