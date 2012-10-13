<?php

class Data_Source_File extends Data_Source_Abstract
{
	public function __construct ()
	{
		Loader::load ('Data_Mapper_File');
		$this->setDataMapper (new Data_Mapper_File);
	}
	
}