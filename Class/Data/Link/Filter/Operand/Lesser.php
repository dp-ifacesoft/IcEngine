<?php

/**
 * Оператор "меньше"
 *
 * @author morph
 */
class Data_Link_Filter_Operand_Lesser extends Data_Link_Filter_Operand_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($left, $right)
	{
		return $left < $right;
	}
}
