<?php
/**
 *
 * @desc Источник данных - MongoDB
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Data_Source_Mongo extends Data_Source_Abstract
{
	public function __construct ()
	{
		$this->setDataMapper (new Data_Mapper_Mongo);
		$this->_mapper->initFilters ();
	}

}