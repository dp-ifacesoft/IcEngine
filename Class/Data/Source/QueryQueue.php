<?php

class Data_Source_QueryQueue extends Data_Source_Abstract
{
	public function __construct ()
	{
		$this->setDataMapper (new Data_Mapper_QueryQueue ());
	}
}