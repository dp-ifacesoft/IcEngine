<?php

/**
 * 
 * Стандартный фильтр чисел.
 * @author Юрий
 *
 */

class Filter_Integer extends Filter_Abstract
{
	
	public function filter ($data)
	{
		return Helper_String::str2int (trim ($data));
	}
	
}