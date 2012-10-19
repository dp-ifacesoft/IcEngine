<?php

Loader::load("Helper_Diff");

abstract class Diff_Renderer
{
	protected function renderTemplate($field)
	{
		$view = View_Render_Manager::pushViewByName ('Smarty');
		try
		{
			$view->assign ('field',$field);
			$view->assign ('renderer',$this);
			return $view->fetch( $this->template() );
		}
		catch (Exception $e)
		{
			$msg =
				'[' . $e->getFile () . '@' .
				$e->getLine () . ':' .
				$e->getCode () . '] ' .
				$e->getMessage () . PHP_EOL;

			error_log (
				$msg . PHP_EOL .
				$e->getTraceAsString () . PHP_EOL,
				E_USER_ERROR, 3
			);
			Debug::log ($msg);
			$result ['error'] = 'Controller_Manager: Error in template.';
		}
		View_Render_Manager::popView ();
	}
	public function valueTemplate()
	{
		return "Controller/".str_replace("_","/",get_class($this))."/value.tpl";
	}
	public function editTemplate()
	{
		return "Controller/".str_replace("_","/",get_class($this))."/edit.tpl";
	}
	protected function template()
	{
		return "Controller/".str_replace("_","/",get_class($this));
	}
	public function render($field)
	{
		return $this->renderTemplate($field);
	}
}

abstract class Diff_Renderer_ValueType extends Diff_Renderer
{
}

class Diff_Renderer_String extends Diff_Renderer_ValueType
{
}
class Diff_Renderer_List extends Diff_Renderer 
{
	private $_list;
	public function getList()
	{
		return $this->_list;
	}
	public function getListItemById($id)
	{
		foreach($this->_list as $li)
		{
			if ($li->id==$id)
			{
				return $li->name;
			}
		}
		return "";
	}
	
	public function render($field)
	{
		if ($field->type->config()->get_list)
		{
			$handler = $field->type->config()->get_list;
			$this->_list = Executor::execute($handler,array($field));
		} 
		else
			$this->_list = Model_Collection_Manager::create($field->type->config()->model_class);
		return parent::render($field);
	}
	
}

class Diff_Renderer_ForeignKey extends Diff_Renderer_List {
	
}

class Diff_Renderer_ManyToMany extends Diff_Renderer_List
{

}

class Diff_Renderer_OneToMany extends Diff_Renderer_List
{
	public function fieldValueIsDeleted($field,$v)
	{
		
		foreach($field->edits as $edit)
		{
			if (in_array($v->id,$edit->value->asArray()))
				return false;
		}
		return true;
	}
	
	public function render($field)
	{
		foreach($field->value as $v)
		{
			$v->set('childRenderer', new Helper_Diff_Renderer($v));
		}
		return parent::render($field);
	}
}

class Helper_Diff_Renderer
{
	private $model;
	
	public function model()
	{
		return $this->model;
	}
	
	public function Helper_Diff_Renderer($model)
	{
		$this->model = $model;
	}
	
	
	public function getModelEdits()
	{
		$edits = Model_Collection_Manager::byQuery(
			'Edit',
			Query::instance()
				->where('model_class',get_class($this->model))
				->where('model_id',$this->model->id)
		);
		foreach($edits as $k => $edit)
		{
			$edits[$k]->data('diff', $this->getDiff($edit));
		}
		return $edits;
	}	
	
	private function getDiff($edit)
	{
		$rows = DDS::Execute(
				Query::instance()
					->select("Edit_Field.name","Edit_Value.value")
					->from("Edit_Value")
					->innerJoin("Edit_Field","Edit_Field.id=Edit_Value.Edit_Field__id")
					->where('Edit_Field.Edit__id',$edit->id)
		)->getResult()->asTable();
		$values = array();
		foreach($rows as $row)
		{
			$name = $row['name'];
			if (array_key_exists($name, $values))
			{
			} 
			else
			{
				$values[$name] = new Objective();
				$values[$name]->value = array();
			}
			$values[$name]->value[] = $row['value'];
		}
		return new Objective($values);
	}
	
	public function render()
	{
		$edits = $this->getModelEdits();
		$model_class = get_class($this->model);
		$fields = array();
		foreach(Helper_Diff::config()->$model_class->fields as $fieldName => $fieldConfig)
		{
			$fieldType = Helper_Diff_Field_Factory::getFieldType($fieldConfig);
			$diff_values = array();
			foreach($edits as $edit)
			{
				$diff = $edit->data('diff');
				$diff_values[$edit->id] = array( 'value' => $diff->$fieldName->value );
			}
			$fields[] = new Objective(array(
				'name' => $fieldName,
				'type' => $fieldType,
				'renderer' => Helper_Diff_Field_Factory::getFieldRenderer($fieldType),
				'value' => $this->model->$fieldName,
				'edits' => $diff_values
			));
		}
		return Controller_Manager::htmlUncached("Diff_Renderer/index", array('renderer' => $this,'fields' => $fields));
	}
}