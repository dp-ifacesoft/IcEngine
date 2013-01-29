<?php

abstract class Collector_Garbage_Abstract extends Model_Factory_Delegate
{
	protected static $_scheme = array (
		'fields'	=> array (
			'id'	=> array (
				'type'		=> 'int',
				'auto_inc'	=> 'true'
			),
			'name'	=> array (
				'type'		=> 'varchar',
				'size'		=> 32,
				'comment'	=> 'Модель коллектора'
			),
			'data'	=> array (
				'type'		=> 'string'
			)
		),
		'keys'		=> array (
			array (
				'primary'	=> 'id'
			),
			array (
				'unique'	=> 'name'
			)
		)
	);
	
	abstract public function process ();
}