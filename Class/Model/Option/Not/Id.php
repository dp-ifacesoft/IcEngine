<?php
/**
 *
 * @desc Опция для отсеивания по id.
 * Ожадаются параметры $ids с массивом первичных ключей или $id с
 * единичным первичным ключом
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Not_Id extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	protected $queryName = 'Not_Id';
}