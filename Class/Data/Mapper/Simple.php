<?php
/**
 * 
 * @desc Simple
 * @author Shvedov_U
 * @package IcEngine
 * 
 */
class Data_Mapper_Simple extends Data_Mapper_Abstract {
	
	/**
	 * @desc 
	 * @param string $model 
	 * @return string
	 */
	public function getTable ($model)
	{
		return Model_Scheme::table ($model);
	}
	
	/**
	 * @desc 
	 * @param string $table 
	 * @return string
	 */
	public function getModel ($table)
	{
		return Model_Scheme::tableToModel ($table);
	}
	
}
