<?php

/**
 * @desc Тип ссылки "многие-ко-многим"
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_ManyToMany extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @desc Таблицы для связи многие-ко-многим
	 * @var array
	 */
	protected static $_tables;

	/**
	 * @desc Получить поле
	 * @return Model_Mapper_Scheme_Field_Abstract
	 */
	protected function _field ()
	{
		return Model_Mapper::field (
			'Int',
			array (
				'Size'		=> 11,
				'Default'	=> 0,
				'Not_Null'
			)
		);
	}

	/**
	 * @desc Сформировать ключ связи "многие-ко-многим"
	 * @param string $model_name
	 * @return string
	 */
	public function key ($model_name)
	{
		$key_table = array ($model_name, $this->getModel ());
		sort ($key_table);
		$key = implode ('_', $key_table);
		$postfix = abs (crc32 ($key_table [0]) % crc32 ($key_table [1]));
		$key .= $postfix;
		return $key;
	}

	/**
	 * @desc Получить схему
	 * @param string $link_table
	 * @param string $from_table
	 * @param string $to_table
	 * @return Model_Mapper_Scheme_Abstract
	 */
	protected function _scheme ($link_table, $from_table, $to_table)
	{
		$model = new Model_Proxy (
			$link_table,
			array (
				'id'	=> 0
			)
		);
		$scheme = Model_Mapper::scheme ($model);
		$scheme->id = Model_Mapper::field (
			'Int',
			array (
				'Size'	=> 11,
				'Auto_Increment',
				'Not_Null'
			)
		);
		$fields = array ($from_table, $to_table);
		foreach ($fields as $field)
		{
			$scheme->$field = $this->_field ();
		}
		$index = 'id_index';
		$unique = $from_table . '_' . $to_table;
		$scheme->$index = Model_Mapper::index (
			'Primary', array ('id')
		);
		$scheme->$unique = Model_Mapper::index (
			'Unique', array ($from_table, $to_table)
		);
		return $scheme;
	}

	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
		$key = $this->key ($model_name);
		$link_table = $key;
		$fields = $this->getField ();
		$link_fields = array ($model_name . '__id', $this->getModel () . '__id');
		sort ($link_fields);
		$fromField = $link_fields [0];
		$toField = $link_fields [1];
		if (!isset (self::$_tables [$key]))
		{
			if (isset ($fields [1]) && is_array ($fields [1]))
			{
				$link_table = $fields [0];
				$fields = $fields [1];
			}
			if ($fields)
			{
				sort ($fields);
				$fromField = $fields [0];
				$toField = $fields [1];
			}
			$exists =  (bool) Helper_Data_Source::table ($link_table);
			self::$_tables [$key] = array (
				'name'		=> $link_table,
				'field'		=> $fields,
				'exists'	=> $exists
			);
			if (!$exists)
			{
				$scheme = $this->_scheme ($link_table, $fromField, $toField);
				$view = Model_Mapper_Scheme_Render_View::byName ('Mysql');
				$sql = $view->render ($scheme);
				mysql_query ($sql);
			}
		}
		$field = $fromField;
		$dest_field = $toField;
		if ($fromField != $model_name . '__id')
		{
			$field = $toField;
			$dest_field = $fromField;
		}
		$query = Query::instance ()
			->select ($dest_field)
			->from ($link_table)
			->where ($field, $id);
		$ids = DDS::execute ($query)->getResult ()->asColumn ();
		$collection = Model_Collection_Manager::byQuery (
			$this->getModel (),
			Query::instance ()
				->where (Model_Scheme::keyField ($this->getModel ()), $ids)
		);
		return $this->resource ()
			->setItems ($collection->items ());
	}

	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::resource()
	 */
	public function resource ()
	{
		return new Model_Mapper_Scheme_Resource_ManyToMany ($this);
	}
}