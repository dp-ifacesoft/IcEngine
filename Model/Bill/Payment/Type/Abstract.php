<?php
/**
 *
 * @desc Абстрактный класс типа платежа
 * @author Гурус
 * @package IcEngine
 *
 */
class Bill_Payment_Type_Abstract extends Model_Factory_Delegate
{

	/**
	 * @desc Конфиг загружен
	 * @var boolean
	 */
	protected $_configLoaded = false;

	/**
	 * @desc Собрать информацию о платежах
	 * @return integer Количество обработанных платежей
	 */
	public function assemble ()
	{
		return 0;
	}

	/**
	 * @desc Моментальный платеж
	 * @param array $params
	 * 		$params ['value'] integer
	 * 		$params ['transactionNo'] string
	 * 		$params ['wallet'] string
	 * 		$params ['details'] string
	 * 		$params ['Bill__id'] integer
	 * @return Bill_Payment
	 */
	public function instantPayment (array $params)
	{
		$bill = Model_Manager::byKey('Bill', $params ['Bill__id']);
		
		if (!$bill)
		{
			return;
		}

		$payment = Model_Manager::byQuery (
			'Bill_Payment',
			Query::instance ()
				->where ('Bill__id', $params ['Bill__id'])
		);

		if ($payment)
		{
			return;
		}

		$payment = new Bill_Payment (array (
			'Bill__id'				=>
				isset ($params ['Bill__id']) ?
				(int) $params ['Bill__id'] : 0,
			'value'					=> $params ['value'],
			'balance'				=> $params ['balance'],
			'Bill_Payment_Type__id'	=> $this->key (),
			'wallet'				=>
				isset ($params ['wallet']) ?
				$params ['wallet'] : '',
			'transactionNo'			=>
				isset ($params ['transactionNo']) ?
				$params ['transactionNo'] : '',
			'details'				=>
				isset ($params ['details']) ?
				$params ['details'] : '',
			'beginProcessTime'		=> Helper_Date::toUnix (),
			'endProcessTime'		=> '2000-01-01 00:00:00',
			'User__id' => $bill->User__id
		));

		$payment->save ();

		// Приемщики платежей
		$acceptors = Model_Collection_Manager::create ('Bill_Payment_Acceptor')
			->addOptions ('::Active', '::Sort');

		foreach ($acceptors as $acceptor)
		{
			$acceptor->accept ($payment, $params);
		}

		return $payment;
	}

}
