<?php
/**
 * 
 * @desc Контроллер платежей
 * @author Гурус
 * @package IcEngine
 *
 */
class Controller_Payment extends Controller_Abstract
{
	
	/**
	 * @desc Собрать новые смс и платежи 
	 * (такие как A1Lite, A1Sms)
	 */
	public function assemble ()
	{
		$types = Model_Collection_Manager::create ('Bill_Payment_Type')
			->addOptions('::Active');
		
		foreach ($types as $type)
		{
			$count = $type->assemble ();
			$type->data ('count', $count);
		}
		$this->_output->send (array (
			'types'	=> $types
		));
	}
	
}