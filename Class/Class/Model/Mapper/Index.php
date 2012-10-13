<?php

class Model_Mapper_Index
{
	public static function factory ($name)
	{
		$class_name = 'Model_Mapper_Index_' . $name;
		Loader::load ($class_name);
		return new $class_name;
	}
}