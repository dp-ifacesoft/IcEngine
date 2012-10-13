<?php

class Model_Mapper_Index_Abstract
{
	protected $_field;
	protected $_scheme;
	protected $_model;
	protected $_value;

	public function getField ()
	{
		return $this->_field;
	}

	public function setField ($field)
	{
		$this->_field = $field;
	}

	public function getName ()
	{
		return substr (get_class ($this), 19);
	}

	public function getModel ()
	{
		return $this->_model;
	}

	public function setModel ($model)
	{
		$this->_model = $model;
	}

	public function getScheme ()
	{
		return $this->_scheme;
	}

	public function setScheme ($scheme)
	{
		$this->_scheme = $scheme;
	}

	public function getValue ()
	{
		return $this->_value;
	}

	public function setValue ($value)
	{
		$this->_value = $value;
	}
}