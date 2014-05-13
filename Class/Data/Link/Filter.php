<?php

/**
 * Фильтр данных
 *
 * @author morph
 */
class Data_Link_Filter
{
	/**
	 * Левая часть условия
	 *
	 * @var string
	 */
	protected $left;

	/**
	 * Оператор
	 *
	 * @var Data_Link_Filter_Operand_Abstract
	 */
	protected $operand;

	/**
	 * Правая часть
	 *
	 * @var string
	 */
	protected $right;

	/**
	 * Конструктор
	 *
	 * @param string $left
	 * @param Data_Link_Filter_Operand_Abstract $operand
	 * @param string $right
	 */
	public function __construct($left, $operand, $right = null)
	{
		$this->left = $left;
		$this->operand = $operand;
		$this->right = $right;
	}

	/**
	 * Получить левую часть
	 *
	 * @return string
	 */
	public function getLeft()
	{
		return $this->left;
	}

	/**
	 * Получить оператор
	 *
	 * @return Data_Link_Filter_Operand_Abstract
	 */
	public function getOperand()
	{
		return $this->operand;
	}

	/**
	 * Получить правую часть
	 *
	 * @return string
	 */
	public function getRight()
	{
		return $this->right;
	}

	/**
	 * Изменить левую часть
	 *
	 * @param string $left
	 */
	public function setLeft($left)
	{
		$this->left = $left;
	}

	/**
	 * Изменить оператор
	 *
	 * @param Data_Link_Filter_Operand_Abstract $operand
	 */
	public function setOperand($operand)
	{
		$this->operand = $operand;
	}

	/**
	 * Изменить правую часть
	 *
	 * @param string $right
	 */
	public function setRight($right)
	{
		$this->right = $right;
	}
}