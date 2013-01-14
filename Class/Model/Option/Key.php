<?php
/**
 * 
 * @desc Опция для выбора по первичному ключу
 * @param string|integer|array ['key'] 
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Option_Key extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	protected $queryName = 'Key';
}