<?php
/**
 *
 * @desc Контроллер регистрации
 * @author Гурус
 * @package IcEngine
 *
 */
class Controller_Registration extends Controller_Abstract
{

	/**
	 * @desc Начало регистрации
	 */
	public function index ()
	{
		if (User::authorized ())
		{
			// Пользователь уже зарегистрирован
			Helper_Header::redirect ('/');
			die ();
		}
	}

	/**
	 * @desc Подтверждение email
	 * @return boolean true, если регистрация закончилась успешно. Иначе false.
	 */
	public function emailConfirm ()
	{
		if (User::authorized ())
		{
			Helper_Header::redirect ('/');
			return;
		}

		$registration = Registration::byCode (
			$this->_input->receive ('code')
		);

		if (!$registration)
		{
			$this->_task->setClassTpl (__METHOD__, 'fail_code_uncorrect');
			return false;
		}
		elseif ($registration->finished)
		{
			$this->_task->setClassTpl (__METHOD__, 'fail_already_finished');
			return false;
		}

		$registration->finish ();

		return true;
	}

	public function postForm ()
	{
		$data = Helper_Form::receiveFields (
			$this->_input,
			Config_Manager::get ('Registration')->fields
		);

		$registration = Registration::tryRegister ($data);
		$this->_output->send ('registration', $registration);

		if (is_array ($registration))
		{
			// произошла ошибка

			$this->_task->setClassTpl (reset ($registration));

			$this->_output->send (array (
				'registration'	=> $registration,
				'data'			=> array (
				'field'			=> key ($registration),
				'error'		=> current ($registration)
				)
			));

			return ;
		}

		$this->_output->send (array (
			'registration'	=> $registration,
			'data'			=> array (
				'removeForm'	=> true
			)
		));
	}

	public function success ()
	{

	}

}