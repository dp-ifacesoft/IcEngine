<?php

class Cross_Site_Data_Coder_Serialize extends Cross_Site_Data_Coder_Abstract
{
	
	public function encode (array $message)
	{
		return serialize ($message);
	}
	
	public function decode ($message)
	{
		return unserialize ($message);
	}
	
}