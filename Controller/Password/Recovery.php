<?php
/**
 *
 * @desc Контроллер востановления пароля.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Password_Recovery extends Controller_Abstract
{

	public function index ()
	{
		$code = Request::get ('code');

		if ($code)
		{
			$recovery = Password_Recovery::byCode ($code);
			if ($recovery && $recovery->active)
			{
				$recovery->startSession ();

				$this->_task->setClassTpl (__METHOD__, 'code_ok');
				return ;
			}
			else
			{
				$this->_task->setClassTpl (__METHOD__, 'code_fail');
				return ;
			}
		}

		Password_Recovery::resetSession ();
	}

	public function change ()
	{
		$password = $this->_input->receive ('password');
		if (strlen ($password) < 3)
		{
			$this->_output->send ('data', array (
				'error'	=> true
			));

			$this->_task->setClassTpl (__METHOD__, 'error_short_password');

			return ;
		}

		$recovery = Password_Recovery::fromSession ();

		if (!$recovery)
		{
			$this->_output->send ('data', array (
				'error'	=> true
			));

			$this->_task->setClassTpl (__METHOD__, 'error_recovery_not_found');

			return ;
		}

		// меняем пароль и делаем неактивной смену
		$recovery->update (array (
			'active'	=> 0
		));

		$recovery->User->update (array (
			'password'	=> $password
		));

		$this->_output->send ('data', array (
			'removeForm'	=> true
		));

		Password_Recovery::resetSession ();
	}

	public function sendCode ()
	{
		$email = $this->_input->receive ('email');
		$query_count = Password_Recovery::queryCountOnEmail ($email);

		// лимит запросов на e-mail
		if ($query_count >= Password_Recovery::MAX_QUERY_PER_EMAIL)
		{
			return $this->_sendError ('error_email_limit');
		}

		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('email', $email)
		);

		if (!$user)
		{
			return $this->_sendError ('error_email_not_found');
		}

		$this->_output->send ('data', array (
			'removeForm' => true
		));

		// Всё правильно, создаем письмо с кодом
		if (!Password_Recovery::sendRecoveryEmail ($user->id, $email))
		{
			return $this->_sendError ('error_sendmail');
		}
	}

}