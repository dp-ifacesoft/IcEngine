<?php
/**
 * 
 * @desc Контроллер каптчи
 * @package IcEngine
 * 
 */
class Controller_Captcha extends Controller_Abstract
{
	
	/**
	 * @desc Для отдачи в аджакс кода, который позже должен быть 
	 * передан постом.
	 */
	public function getCode ()
	{
		Loader::load ('Helper_Captcha');
		
		$code = Helper_Captcha::generateAutocode ();
		
		$_SESSION [Helper_Captcha::SF_AUTO_CODE] = $code;
		
		$this->_output->send ('data', array (
			'code'	=> $code
		));
		
		$this->_task->setTemplate (null);
	}
	
	/**
	 * @desc Создаем и выводим каптчу в форму
	 */
	public function captchaInput ()
	{
		Loader::load ('Helper_Captcha');
		
		$code = Helper_Captcha::generateAutocode ();
		
		$_SESSION [Helper_Captcha::SF_AUTO_CODE] = $code;
		
		$this->_output->send (array (
			'code'	=> $code
		));
	}
	
	/**
	 * @desc Скрытый инпут автокаптчи
	 */
	public function acaptchaInput ()
	{
		Loader::load ('Helper_Captcha');
		
	}
	
	
	
}
