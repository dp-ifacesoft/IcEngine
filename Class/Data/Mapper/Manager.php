<?php
/**
 * 
 * @desc Manager
 * @author Shvedov_U
 * @package IcEngine
 * 
 */
class Data_Mapper_Manager
{
	
	/**
	 * @desc
	 * @var array of Data_Mapper_Abstract
	 */
	protected static $_mappers = array();
	
	/**
	 * @desc 
	 * @param string $name 
	 * @return Data_Mapper_Abstract
	 */
	public static function get ($name)
	{
		if (!isset (self::$_mappers [$name]))
		{
			Loader::load ('Data_Mapper_Abstract');
			$class = 'Data_Mapper_' . $name;
			Loader::load ($class);
			self::$_mappers [$name] = new $class;
		}
		
		return self::$_mappers [$name];
	}
	
}
