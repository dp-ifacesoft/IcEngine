<?php

abstract class Cross_Site_Data_Coder_Abstract extends Model_Factory_Delegate
{
	
	/**
	 * 
	 * @param array $message
	 * @return string
	 */
	abstract public function encode (array $message);
	
	/**
	 * 
	 * @param string $message
	 * @return array
	 */
	abstract public function decode ($message);
	
}