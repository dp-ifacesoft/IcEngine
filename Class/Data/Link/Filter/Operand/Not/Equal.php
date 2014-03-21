<?php

/**
 * Оператор неравенства
 *
 * @author morph
 */
class Data_Link_Filter_Operand_Not_Equal extends
	Data_Link_Filter_Operand_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($left, $right)
	{
		return $left !== $right;
	}
}