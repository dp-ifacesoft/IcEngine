<?php

class Data_Source_Defined extends Data_Source_Abstract
{
	public function __construct ()
	{
		Loader::load ('Data_Mapper_Defined');
		$this->setDataMapper (new Data_Mapper_Defined);
	}
	
}