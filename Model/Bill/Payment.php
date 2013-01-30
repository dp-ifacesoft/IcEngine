<?php

/**
 * 
 * @desc Платеж по счету
 * @author Гурус
 * @package IcEngine
 *
 */
class Bill_Payment extends Model
{

	public function change($sum, $comment, $model, $service, $discount)
	{

		$this->User->component('Balance', 0)->change($sum, $comment, $model, $service, $discount);
	}

}