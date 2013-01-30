<?php

/**
 * @desc Представление рендера схемы связей модели для Mysql
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_View_Mysql extends Model_Mapper_Scheme_Render_View_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_View_Abstract::render
	 */
	public static function render ($scheme)
	{
		$model = $scheme->getModel ();
		$model_name = $model->modelName ();

		$model_config = Config_Manager::get ('Model_Mapper_' . $model_name);

		if (!$model_config)
		{
			return;
		}

		$query = Query::instance ()
			->createTable ($model_name);

		foreach ($model_config ['fields']->__toArray () as
			$field_name => $values)
		{
			$field = new Model_Field ($field_name);
			$field->setType ($values [0]);
			$attr = $values [1];
			foreach ($attr as $key => $value)
			{
				if (is_numeric ($key))
				{
					unset ($attr [$key]);
					$attr [$value] = true;
				}
			}
			if (!empty ($attr ['Size']))
			{
				$field->setSize ($attr ['Size']);
			}
			if (!empty ($attr ['Enum']))
			{
				$field->setEnum ($attr ['Enum']);
			}
			$field->setNullable (!empty ($attr ['Null']));
			if (!empty ($attr ['Unsigned']))
			{
				$field->setUnsigned (true);
			}
			if (!empty ($attr ['Charset']))
			{
				$field->setCharset ($attr ['Charset']);
			}
			if (isset ($attr ['Default']))
			{
				$field->setDefault ($attr ['Default']);
			}
			if (!empty ($attr ['Comment']))
			{
				$field->setComment ($attr ['Comment']);
			}
			if (!empty ($attr ['Auto_Increment']))
			{
				$field->setAutoIncrement (true);
			}
			$query->addField ($field);
		}

		if ($model_config ['indexes'])
		{
			foreach ($model_config ['indexes']->__toArray () as
				$index_name => $values)
			{
				$index = new Model_Index ($index_name . '_index');
				$index->setType ($values [0]);
				$index->setFields ($values [1]);
				$query->addIndex ($index);
			}
		}
		return $query;
	}
}
