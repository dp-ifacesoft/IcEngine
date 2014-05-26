<?php
/**
 *
 * @desc Контроллер активаций.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Sms_Activation extends Controller_Abstract
{

	/**
	 * @desc Отправка сообщения с кодом
	 */
	public function sendCode ()
	{
		list (
			$phone
		) = $this->_input->receive (
			'phone'
		);
		
		$activation = Helper_Activation::newShortCode ($phone);



		$this->_output->send (array (
			'activation'	=> $activation,
			'data'			=> array (
				'activation_id'	=> $activation->id
			)
		));
	}

}