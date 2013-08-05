<?php

/**
 * Провайдер консольного приложения
 * 
 * @author morph, goorus
 */
class Data_Provider_Cli extends Data_Provider_Buffer
{
	/**
	 * @inheritdoc
	 */
	public function __construct()
	{
		$this->buffer = &$_SERVER['argv'];
	}
}