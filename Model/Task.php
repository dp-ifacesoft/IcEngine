<?php

/**
 * @desc Задание для планировщика
 * @author Илья Колесников
 * @package IcEngine
 * @copyright i-complex.ru
 */
class Task extends Model
{
	protected static $_scheme = array (
		'fields'	=> array (
			'id'		=> array (
				'type'		=> 'int',
				'auto_inc'	=> true
			),
			'action'	=> array (
				'type'		=> 'varchar',
				'size'		=> 64
			),
			'period'	=> array (
				'type'		=> 'int',
			),
			'lastTime'	=> array (
				'type'		=> 'datetime'
			),
			'active'	=> array (
				'type'		=> 'tinyint',
				'default'	=> 1
			)
		),
		'keys'		=> array (
			array (
				'primary'	=> 'id'
			)
		)
		
	);
}