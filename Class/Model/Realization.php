<?php

class Model_Realization extends Model
{
	private $_model;

	protected $_fields;

	public function __construct ($model, $fields)
	{
		$this->_fields = $fields;

		$this->_model = $model;
	}

	public function __get ($field)
	{
		if (array_key_exists ($field, $this->_fields))
		{
			return $this->_fields [$field];
		}

		return $this->_model->__get ($field);
	}

	public function additions ()
	{
		return $this->_fields;
	}

	public function sfield ($field)
	{
		if (array_key_exists ($field, $this->_fields))
		{
			return $this->_fields [$field];
		}

		return $this->_model->sfield ($field);
	}

	public function __set ($field, $value)
	{
		if (!$this->_model->isLoaded ())
		{
			$this->_model->load ();
		}

		if (array_key_exists ($field, $this->_model->asRow ()))
		{
			return $this->_model->field ($field, $value);
		}

		$this->_fields [$field] = $value;
	}

	public function field ($key)
	{
		if (func_num_args () > 1)
		{
			$this->__set ($key, func_get_arg (1));
		}
		else
		{
			return $this->__get ($key);
		}
	}

	public function asRow ()
	{
		return array_merge (
			$this->_fields,
			$this->_model->asRow ()
		);
	}

	public function getFields ()
	{
		return $this->asRow ();
	}

	public function __call ($method, $params)
	{
		if (method_exists ($this->_model, $method))
		{
			return call_user_func_array (
				array ($this->_model, $method),
				$params
			);
		}

		if (strlen ($method) > 3 && strncmp ($method, 'get', 3) == 0)
		{
			return $this->_model->attr (
				strtolower ($method [3]) .
				substr ($method, 4)
			);
		}
		
		throw new Model_Exception ("Method $method not found");
	}

	public function __isset ($key)
	{
		return $this->hasField ($key);
	}

	protected function _joint ($model, $key = null)
	{
		if ($key !== null)
		{
			$this->_model->setJoint (
				$model,
				Model_Manager::byKey ($model, $key)
			);
		}

		return $this->_model->getJoint ($model);
	}

	public function component ($type)
	{
		return call_user_func_array (
			array ($this->_model, __METHOD__),
			func_get_args ()
		);
	}

	public function attr ($key)
	{
		return call_user_func_array (
			array ($this->_model, __METHOD__),
			func_get_args ()
		);
	}

	public function className ()
	{
		return $this->_model->className ();
	}

	public function data ($key)
	{
		return call_user_func_array (
			array ($this->_model, __METHOD__),
			func_get_args ()
		);
	}

	public function delete ()
	{
		return $this->_model->delete ();
	}

	public function getAttribute ($key)
	{
		return Attribute_Manager::get ($this->_model, $key);
	}

	public function hasField ($field)
	{
		return $this->_model->hasField ($field) ||
			array_key_exists ($field, $this->_fields);
	}

	public function key ()
	{
		return $this->_model->key ();
	}

	public function modelName ()
	{
		return get_class ($this->_model);
	}

	public function reset ()
	{
		$this->_fields = array ();
		$this->_model->reset ();
	}

	public function save ($hard_insert = false)
	{
		return $this->_model->save ($hard_insert);
	}

	public function set ($field, $value = null)
	{
		if (!$this->_model->isLoaded ())
		{
			$this->_model->load ();
		}

		if (array_key_exists ($field, $this->_model->asRow ()))
		{
			return $this->_model->field ($field, $value);
		}

		$this->_model->set ($field, $value);
	}

	public function setAttribute ($key, $value = null)
	{
		Attribute_Manager::set ($this->_model, $key, $value);
	}

	public function title ()
	{
		return $this->_model->title ();
	}

	public function load ()
	{
		return $this->_model->load ();
	}

	public function validate ($fields)
	{
		return $this->_model->validate ($fields);
	}

	public function unsetField ($name)
	{
		if (array_key_exists ($name, $this->_fields))
		{
			unset ($this->_fields [$name]);
			return $this;
		}
		else
		{
			return $this->_model->unsetField ($name);
		}
	}

	public function update (array $data)
	{
		if (!$this->_model->isLoaded ())
		{
			$this->_model->load ();
		}

		foreach ($data as $field=>$value)
		{
			if (!$this->_model->hasField ($field))
			{
				$this->_fields [$field] = $value;
				unset ($data [$field]);
			}
		}

		if ($data)
		{
			return $this->_model->update ($data);
		}
	}

	public function updateCarefully (array $data)
	{
		if (!$this->_model->isLoaded ())
		{
			$this->_model->load ();
		}

		foreach ($data as $field=>$value)
		{
			if (!$this->_model->hasField ($field))
			{
				$this->_fields [$field] = $value;
				unset ($data [$field]);
			}
		}

		if ($data)
		{
			return $this->_model->updateCarefully ($data);
		}
	}
}
