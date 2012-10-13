<?php

class Data_Source_Mysql extends Data_Source_Abstract
{
	public function __construct ()
	{
		$this->setDataMapper (new Data_Mapper_Mysql ());
		$this->_mapper->initFilters ();
	}
}