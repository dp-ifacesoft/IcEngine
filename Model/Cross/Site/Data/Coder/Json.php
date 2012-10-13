<?php

class Cross_Site_Data_Coder_Json extends Cross_Site_Data_Coder_Abstract
{
	
	public function encode (array $message)
	{
		return json_encode ($message);
	}
	
	public function decode ($message)
	{
		return json_decode ($message, true);
	}
	
}