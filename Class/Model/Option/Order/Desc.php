<?php
/**
 *
 * @desc Опция для добавления правила упорядочивания в порядке убывания.
 * Возможно передать поле для сортировки
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Order_Desc extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	protected $queryName = 'Order_Desc';
}