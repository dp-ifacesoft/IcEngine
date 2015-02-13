<?php

/**
 * Оператор равенства
 *
 * @author morph
 */
class Data_Link_Filter_Operand_Equal extends Data_Link_Filter_Operand_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($left, $right)
	{
		return $left === $right;
	}
}