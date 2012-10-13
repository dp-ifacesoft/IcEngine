<?php

/**
* @desc  
*/
class Attribute extends Model
{
	public static function jsonDecode ($p)
	{
		$tmp = json_decode ($p);
		return (!$tmp) ? json_encode ($p, true) : $tmp;
	}
}