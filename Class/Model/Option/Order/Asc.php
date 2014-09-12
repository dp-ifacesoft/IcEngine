<?php
/**
 *
 * @desc Опция для добавления правила упорядочивания в порядке возрастания.
 * Возможно передать поле для сортировки
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Order_Asc extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	protected $queryName = 'Order_Asc';
}