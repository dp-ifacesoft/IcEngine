<?php
/**
 * 
 * @desc Опция для сортировки по полю "sort".
 * Если $params ['order'] == 'desc', данные будут отсортированы в обратном
 * порядке.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Sort extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	protected $queryName = 'Sort';
}