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
}

class Diff_String extends Diff_ValueType
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
	protected function compareLists($a,$b)
	{
		if (!$a && !$b)
			return true;
		if ($a instanceof Model_Collection && $b instanceof Model_Collection)
		{
			$diff = $a->diffEdit($b);
			if (
					$diff[Model_Collection::DIFF_EDIT_ADD]->count()==0 &&
					$diff[Model_Collection::DIFF_EDIT_DEL]->count()==0
				)
					return true;
			$result = true;

			$modified = $diff[Model_Collection::DIFF_EDIT_NO];
			foreach($modified as $model)
			{
				if (!$model->id || !$a->hasByFields($model))
					continue;

				$model_comparer = new Helper_Diff_Comparer(
						$a->filterGetFirst(array( 'id' => $model->id)),
						$b->filterGetFirst(array( 'id' => $model->id)) );
				$compare_result = $model_comparer->compare();
				if (is_array($compare_result) && count($compare_result)>0)
				{
					$result = false;
				} 
			}
			return $result;
			
		}
		if (is_array($a) && is_array($b))
		{
			$result = array_diff($a,$b);
			if (count($result)==0)
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

class Diff_ManyToMany extends Diff_LinkList
{
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
			'User__id'		=> 0
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
