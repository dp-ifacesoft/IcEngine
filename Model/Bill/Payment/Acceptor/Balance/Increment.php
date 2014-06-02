<?php
/**
 * 
 * @desc Примщик платежей для увеличения баланса пользователя на сумму 
 * платежа.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Bill_Payment_Acceptor_Balance_Increment extends Bill_Payment_Acceptor_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		// Комментарий к увеличению баланса
		'increment_comment'		=> 'Пополнение баланса через платеж.'
	);
	
	/**
	 * (non-PHPdoc)
	 * @see Bill_Payment_Acceptor_Abstract::accept()
	 */
	public function accept (Bill_Payment $payment, array $params)
	{
		$config = $this->config ();
		$user = $payment->User;
		$balance = $user->component ('Balance', 0);
		$balance->change ($payment->value, $config ['increment_comment']);
	}
	
}