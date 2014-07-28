<?php

/**
 * Оператор "меньше или равно"
 *
 * @author morph
 */
class Data_Link_Filter_Operand_Lesser_Or_Equal extends
	Data_Link_Filter_Operand_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($left, $right)
	{
		return $left <= $right;
	}
}
