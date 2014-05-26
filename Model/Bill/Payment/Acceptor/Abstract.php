<?php
/**
 * 
 * @desc Абстрактный класс приемщика платежа.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Bill_Payment_Acceptor_Abstract extends Model_Factory_Delegate
{
	
	/**
	 * @desc Прием платежа.
	 * @param Bill_Payment $payment Платеж.
	 * @param array $params Прочие параметры.
	 */
	abstract public function accept (Bill_Payment $payment, array $params);
	
}