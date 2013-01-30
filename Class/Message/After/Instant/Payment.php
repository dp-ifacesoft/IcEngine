<?php
/**
 * 
 * @desc Событие после моментального платежа
 * @author Гурус
 * @package IcEngine
 *
 */
class Message_After_Instant_Payment extends Message_Abstract
{
	
	/**
	 * @desc Возвращает платеж
	 * @return Bill_Payment
	 */
	public function payment ()
	{
		return $this->_data ['payment'];
	}
	
	/**
	 * @desc Добавить в очередь сообщений
	 * @param Bill_Payment $payment
	 * @param array $params
	 * @return Message_After_Instant_Payment
	 */
	public static function push (Bill_Payment $payment, 
		array $params = array ())
	{
		return IcEngine::$messageQueue->push (
			'After_Instant_Payment',
			array_merge (
				$params,
				array (
					'payment'	=> $payment
				)
			)
		);
	}
	
}