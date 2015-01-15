<?php

/**
 * Заглушка для провайдера данных
 * 
 * @author morph
 */
class Data_Provider_Nop extends Data_Provider_Abstract
{
    /**
     * @inheritdoc
     */
	public function get ($key, $plain = false)
	{
		
	}
	
    /**
     * @inheritdoc
     */
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		
	}
}