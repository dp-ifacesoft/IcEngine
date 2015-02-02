<?php

/**
 * Абстрактный оператор фильтрации данных
 *
 * @author morph
 */
abstract class Data_Link_Filter_Operand_Abstract
{
	/**
	 * Фильтрует данных
	 *
	 * @param string $left
	 * @param string $right
	 * @return boolean
	 */
	abstract public function filter($left, $right);
}
