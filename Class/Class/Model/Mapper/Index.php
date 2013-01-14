<?php

class Model_Mapper_Index
{
	public static function factory ($name)
	{
		$class_name = 'Model_Mapper_Index_' . $name;
		return new $class_name;
	}
}