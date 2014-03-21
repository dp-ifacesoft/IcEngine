<?php

class Data_Source_DBI extends Data_Source_Abstract
{
	public function __construct ()
	{
		$this->setDataMapper (new Data_Mapper_DBI ());
	}
}