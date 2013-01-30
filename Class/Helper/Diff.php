<?php

class Helper_Diff
{
	protected static $_config;
	
	public static function config()
	{
		if (!self::$_config)
			self::$_config = Config_Manager::get(__CLASS__,array());
		return self::$_config;
	}	
	
	public static function getChangedModels($model_class)
	{
		$model_class = mysql_real_escape_string($model_class);
		return Model_Collection_Manager::byQuery(
					$model_class,
					Query::instance()
						->innerJoin('Edit',"Edit.model_class='$model_class' AND Edit.model_id=$model_class.id")
						->order("$model_class.id DESC")
						->group("$model_class.id")
				);
	}

}