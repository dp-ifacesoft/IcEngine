<?php

/**
 * @desc Задание планировщика, добавленно в очередь
 * @author Илья Колесников
 * @package IcEngine
 * @copyright i-complex.ru
 */
class Task_Queue extends Model
{
	protected static $_scheme = array (
		'fields'	=> array (
			'id'			=> array (
				'type'		=> 'int',
				'auto_inc'	=> true
			),
			'Task__id'		=> array (
				'type'		=> 'int'
			),
			'createdAt'		=> array (
				'type'		=> 'datetime'
			),
			'finishedAt'	=> array (
				'type'		=> 'datetime'
			),
			'processed'		=> array (
				'type'		=> 'tinyint',
				'default'	=> 0
			)
		), 
		'keys'		=> array (
			array (
				'primary'	=> 'id',
			),
			array (
				'index'		=> 'Task__id',
			),
			array (
				'index'		=> 'createdAt'
			)
		)
	);
}