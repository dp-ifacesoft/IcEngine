<?php

class Helper_Diff
{
	protected static $_config;
	
	public static function config()
	{
		if (!self::$_config)
			self::$_config = Config_Manager::get(__CLASS__,array());
		return self::$_config;
	}

	public static function deleteFieldEdits($field)
	{
		$edit_ids = array();
		foreach($field->edits as $edit)
			$edit_ids[] = $edit->edit->id;
		$edit_field_ids = DDS::execute(
			Query::instance()
				->select("id")
				->from("Edit_Field")
				->where('name',$field->name)
				->where('Edit__id',$edit_ids)
		)->getResult()->asColumn();
		self::deleteFieldEditsByIds($edit_field_ids);
	}
	
	public static function getChangedModels($model_class)
	{
		$model_class = mysql_real_escape_string($model_class);
		return Model_Collection_Manager::byQuery(
					$model_class,
					Query::instance()
						->innerJoin('Edit',"Edit.model_class='$model_class' AND Edit.model_id=$model_class.id")
						->order("$model_class.id DESC")
						->group("$model_class.id")
				);
	}
	
	public static function deleteModelEdits($model)
	{
		$edit_ids = DDS::execute(
			Query::instance()
				->select("id")
				->from("Edit")
				->where('model_class',get_class($model))
				->where('model_id',$model->key())
		)->getResult()->asColumn();
		$edit_field_ids = DDS::execute(
			Query::instance()
				->select("id")
				->from("Edit_Field")
				->where('Edit__id',$edit_ids)
		)->getResult()->asColumn();
		self::deleteFieldEditsByIds($edit_field_ids);
		// delete edits
		DDS::execute(
			Query::instance()
				->delete()
				->from('Edit')
				->where('id',$edit_ids)
		);
	}
	
	public static function deleteFieldEditsByIds($edit_field_ids)
	{
		if (is_array($edit_field_ids) && count($edit_field_ids)>0)
		// delete edit field values
		DDS::execute(
			Query::instance()
				->delete()
				->from('Edit_Value')
				->where('Edit_Field__id',$edit_field_ids)
		);
		// delete edit fields
		DDS::execute(
			Query::instance()
				->delete()
				->from('Edit_Field')
				->where('id',$edit_field_ids)
		);
	}
	
	public static function deleteFieldValue($field,$value)
	{
		$edit_ids = array();
		foreach($field->edits as $edit)
			$edit_ids[] = $edit->edit->id;
		$edit_field_ids = DDS::execute(
			Query::instance()
				->select("id")
				->from("Edit_Field")
				->where('name',$field->name)
				->where('Edit__id',$edit_ids)
		)->getResult()->asColumn();
		DDS::execute(
			Query::instance()
				->delete()
				->from('Edit_Value')
				->where('value',$value)
				->where('Edit_Field__id',$edit_field_ids)
		);
	}
	
	
	public static function addFieldValue($field,$value)
	{
		$edit_ids = array();
		foreach($field->edits as $edit)
			$edit_ids[] = $edit->edit->id;
		$edit_field_id = DDS::execute(
			Query::instance()
				->select("id")
				->from("Edit_Field")
				->where('name',$field->name)
				->where('Edit__id',$edit_ids)
		)->getResult()->asValue();
		DDS::execute(
			Query::instance()
			->insert("Edit_Value")
			->values(array (
                                            'Edit_Field__id' => $edit_field_id,
                                            'value' => $value
                           ))
		);
	}
	public static function packEdits()
	{
		
	}

}