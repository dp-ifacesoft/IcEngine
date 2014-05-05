<?php

/**
 * Оператор проверки на не ноль
 *
 * @author morph
 */
class Data_Link_Filter_Operand_Is_Not_Null extends
	Data_Link_Filter_Operand_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($left, $right)
	{
		return !is_null($left);
	}
}