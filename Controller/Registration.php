<?php

Loader::load ('Registration');

class Controller_Registration extends Controller_Abstract
{
	
	/**
	 * Последняя обработанная регистрация
	 * @var Registration
	 */
	public $registration;
	
	/**
	 * Начало регистрации
	 */
	public function index ()
	{
		View_Render_Broker::getView ()->resources ()->add (
			'/js/Widget/Registration.js');
		
		if (User::authorized ())
		{
			Loader::load ('Header');
			Header::redirect ('/');
			die ();
		}
	}
	
	/**
	 * Подтверждение email
	 * @return boolean
	 * 		True, если регистрация закончилась успешно.
	 * 		Иначе false.
	 */
	public function emailConfirm ()
	{
		$this->registration = Registration::byCode (
			$this->_input->receive ('code'));
		
		if (!$this->registration)
		{
			IcEngine::$application->frontController->getDispatcher ()
				->currentIteration ()->setTemplate (
					str_replace (array ('::', '_'), '/', __METHOD__) .
					'/fail_code_uncorrect.tpl');
			return false;	
		}
		elseif ($this->registration->finished)
		{
			IcEngine::$application->frontController->getDispatcher ()
				->currentIteration ()->setTemplate (
					str_replace (array ('::', '_'), '/', __METHOD__) .
					'/fail_already_finished.tpl');
			return false;
		}
		
		$this->registration->finish ();
		return true;
	}
	
	public function postForm ()
	{
		Loader::load ('Helper_Form');
		$data = Helper_Form::receiveFields ($this->_input, 
			Registration::$config ['fields']);
		
		$result = Registration::tryRegister ($data);
		
		$this->_template = 
			IcEngine::$application->frontController->getDispatcher ()
			->currentIteration ()->setTemplate (
				str_replace (array ('_', '::'), '/', __METHOD__) . 
				'/' . 
				$result . '.tpl');
		
		$this->_output->send ('result', $result);
		
		if ($result == Registration::OK)
		{
			$this->_output->send ('data', array (
				'removeForm'	=> true
			));
		}
	}
	
}