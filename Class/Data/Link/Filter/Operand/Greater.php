<?php

/**
 * Оператор "больше"
 *
 * @author morph
 */
class Data_Link_Filter_Operand_Greater extends Data_Link_Filter_Operand_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($left, $right)
	{
		return $left > $right;
	}
}
