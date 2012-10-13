<?php
/**
 * 
 * @desc Контроллер для работы с Loginza.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
Loader::load ('Controller_Authorization_Abstract');
class Controller_Authorization_Loginza extends Controller_Authorization_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Controller_Authorization_Abstract::authorize()
	 */
	public function authorize ()
	{
		parent::authorize ();
		Loader::load ('Helper_Header');
		$redirect = $this->_output->receive ('redirect');
		Helper_Header::redirect ($redirect);
	}
	
}