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
	protected static $_config = array(
		// Комментарий к увеличению баланса
		'increment_comment' => 'Пополнение баланса через платеж.'
	);

	/**
	 * (non-PHPdoc)
	 * @see Bill_Payment_Acceptor_Abstract::accept()
	 */
	public function accept(Bill_Payment $payment, array $params)
	{
		$config = $this->config();
		$user = $payment->User;
		$balance_collection = $user->component('Balance');
		
		Loader::load('Component_Balance');
		$balance = $balance_collection->isEmpty() ? new Component_Balance(
						array(
							'table' => $user->modelName,
							'rowId' => $user->key(),
							'value' => 0
						)
				) : $balance_collection->item(0);

		$balance->change($payment->value, $config ['increment_comment'], $user);
	}

}