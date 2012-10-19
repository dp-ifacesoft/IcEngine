<?php

class Model_Mapper_Field_Attribute_Not_Null extends Model_Mapper_Field_Attribute_Abstract
{
	public function validate ()
	{
		return (bool) ($this->_value !== null);
	}

}