<?php

Loader::load("Helper_Diff");
Loader::load("Model_Edit");
Loader::load("Model_Edit_Field");
Loader::load("Model_Edit_Value");

abstract class Diff_Field
{
	protected $config;
	public function config()
	{
		return $this->config;
	}
	public function Diff_Field($config)
	{
		$this->config = $config;
	}
	public abstract function isValueType();
	public abstract function compare($a,$b);
	public function getNewValueFromInput($field, $input, $parent = '')
	{
		return $input->receive($parent.$field->name."-new-value");
	}

	public function setNewValue($model, $field, $new_value, $ignore_update = false)
	{
		$model->set($field->name,$new_value);
	}
}

class Diff_ValueType extends Diff_Field
{	
	public function isValueType()
	{
		return true;
	}
	public function compare($a,$b)
	{
		return ($a==$b);
	}
	public function getNewValueFromInput($field, $input,$parent = '')
	{
		return array( parent::getNewValueFromInput($field, $input,$parent) );
	}
	public function setNewValue($model, $field, $new_value, $ignore_update = false)
	{
		parent::setNewValue($model, $field, $new_value[0], $ignore_update);
	}
}

class Diff_String extends Diff_ValueType
{
	
}

class Diff_Int extends Diff_ValueType
{
	
}

class Diff_Geopoint extends Diff_ValueType
{
	
}

class Diff_Bool extends Diff_ValueType
{
	
}

class Diff_ForeignKey extends Diff_ValueType
{
}

abstract class Diff_LinkList extends Diff_Field
{
	public function isValueType()
	{
		return false;
	}
	
	public function diffEdit ($a,$b, $fields = array())
	{
		$model_name = $b->modelName ();
	
		// Коллекция добавленных элементов
		$collection_add = Model_Collection_Manager::create ($model_name)
		->reset ();
	
		// Коллекция неизменненых элементов
		$collection_no = Model_Collection_Manager::create ($model_name)
		->reset ();
	
		// Коллекция удаленных элементов
		$collection_del = Model_Collection_Manager::create ($model_name)
		->reset ();
	
		$collection_count = $a->count ();
	
		foreach ($b as $model)
		{
			$diff_model = $a->hasByFields ($model, $fields);
	
			if ($diff_model)
			{
				$collection_no->add ($diff_model);
				$collection_count--;
			}
			else
			{
				$collection_add->add ($model);
			}
		}
	
		// если $collection_count не 0, делаем вывод, что есть удаленные модели
		if ($collection_count)
		{
			foreach ($a as $model)
			{
				if (!$b->hasByFields ($model, $fields))
				{
					$collection_del->add($model);
				}
			}
		}
	
		return array(
			'added'		=> $collection_add,
			'no'		=> $collection_no,
			'deleted'	=> $collection_del
		);
	}
	
	
	protected function compareLists($a,$b)
	{
		if (!$a && !$b)
			return true;
        if ($a instanceof Model_Collection && $b instanceof Model_Collection)
		{
			$diff = $this->diffEdit($a,$b);
			$modified = $diff['no'];
			$modified->add($diff['added']);
			$result = true;
			foreach($modified as $model)
			{
				if (!$model->id)
					continue;
				Helper_Diff::deleteModelEdits($model);
				$new = $b->filter(array( 'id' => $model->id))->first();
				if (!$a->hasByFields($model))
				{
					$orig = Model_Manager::create(get_class($model),array_keys($model->getFields() ));
					$orig->id = $model->id;
				} else
					$orig = $a->filter(array( 'id' => $model->id))->first();
				$model_comparer = new Helper_Diff_Comparer(
						$orig,
						$new );
				$compare_result = $model_comparer->compare();
				if (is_array($compare_result) && count($compare_result)>0)
				{
					$result = false;
				} 
			}
			if ($diff['added']->count()>0 ||
				$diff['deleted']->count()>0)
					$result = false;
			return $result;
			
		}
		if (is_array($a) && is_array($b))
		{
			if (count(array_diff($a,$b))==0 && count(array_diff($b,$a))==0)
				return true;
		}
		return false;
	}
	public function compare($a,$b)
	{
		return $this->compareLists($a,$b);
	}
}

class Diff_OneToMany extends Diff_LinkList
{
}

class Diff_OneToManyIds extends Diff_LinkList
{
}

class Diff_ManyToMany extends Diff_LinkList
{
	public function setNewValue($model, $field, $new_value, $ignore_update = false)
	{
		parent::setNewValue($model, $field, $new_value, $ignore_update);
                if ($ignore_update)
                    return;
		DDS::execute(
			Query::instance()
				->delete()
				->from( $this->config()->model_transient )
				->where($this->config()->model_transient_fk1,$model->key())
		);
		foreach($new_value as $v)
		{
			$new_row = Model_Manager::create(
				$this->config()->model_transient,
				array(
					$this->config()->model_transient_fk1 => $model->key(),
					$this->config()->model_transient_fk2 => $v
				)
			);
			$new_row->save();
		}
	}
}

class Helper_Diff_Field_Factory
{
	public static function getFieldType($fld_config)
	{
		$className="";
		if (is_object($fld_config))
		{
			$className = $fld_config->type;
		} else {
			$className = new Objective();
		}
		if (!$className)
			$className = "String";
		$className = "Diff_$className";
		$fieldType = new $className($fld_config);
		return $fieldType;
	}
	
	public static function getFieldRenderer($fieldType)
	{
		$className = "Diff_Renderer_" . ($fieldType->config()->renderer ? $fieldType->config()->renderer : str_replace("Diff_","",get_class($fieldType)) );
		$fieldRenderer = new $className();
		return $fieldRenderer;
	}
	
}

class Helper_Diff_Comparer
{
	private $result;
	
	
	public function Helper_Diff_Comparer($a,$b)
	{
		$this->a = $a;
		$this->b = $b;
		$this->result = array();
	}
	
	public function compare()
	{
		if (get_class($this->a)!=get_class($this->b))
			return;
		if ($this->a==$this->b)
			return true;
		$model_class = get_class($this->a);
		$result = array();
		foreach(Helper_Diff::config()->$model_class->fields as $fld_name => $fld_config)
		{
			$fieldType = Helper_Diff_Field_Factory::getFieldType($fld_config);
			if (!$fieldType->compare($this->a->$fld_name,$this->b->$fld_name))
			{
				$result[] = array('name' => $fld_name,'type' => $fieldType,'value' => $this->b->$fld_name );
			}
		}
		$this->result = $result;
		$this->writeDiff();
		return $result;
	}
	
	private function writeDiff()
	{
		$modelEdit = new Edit(array(
			'model_class'	=> get_class($this->a),
			'model_id'		=> $this->a->id,
			'edit_time'		=> Helper_Date::toUnix(),
			'User__id'		=> User::getCurrent()->id
		));
		$modelEdit->save();
		foreach($this->result as $diff_row)
		{
			$fld = new Edit_Field(array(
				'Edit__id'	=> $modelEdit->id,
				'name'		=> $diff_row['name']
			));
			$fld->save();
			$fldType = $diff_row['type'];
			$values = $diff_row['value'];
			if ($fldType->isValueType())
				$values = array($values);
			foreach($values as $value)
			{
				if ($value instanceof Model)
				{
					$value = $value->id;
				}
				$val = new Edit_Value(array(
					'Edit_Field__id' => $fld->id,
					'value' => $value
				));
				$val->save();
			}
		}
	}
	
}
