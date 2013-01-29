<?php
/**
 * 
 * @desc Действие контроллера.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Action extends Model
{
	/**
	 * @desc Флаг игнорирования
	 * @var string
	 */
	const IGNORE_FLAG = 'ignore';
	
	/**
	 * @desc Транспорт входной
	 * @var string
	 */
	public $_input;
	
	/**
	 * @desc Транспорт выходной
	 * @var string
	 */
	public $_output;

}