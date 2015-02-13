<?php

/**
 * Оператор "не содержит"
 *
 * @author morph
 */
class Data_Link_Filter_Operand_Not_Contains extends
	Data_Link_Filter_Operand_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($left, $right)
	{
		return !in_array($right, $left);
	}
}