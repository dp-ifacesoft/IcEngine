<?php
/**
 * @package IcEngine
 */

Loader::load ('Message_Abstract');

class Message_Controller_Action extends Message_Abstract
{
	
	public static function push ($transport, array $params = array ())
	{
		Message_Queue::push (
			'Controller_Action ',
			array_merge (
				$params,
				array ('transport'	=> $transport)
			)
		);
	}
}