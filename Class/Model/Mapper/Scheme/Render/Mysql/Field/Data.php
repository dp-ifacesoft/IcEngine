<?php

/**
 * @desc Дополнительный рендер для полей атрибутов
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Render_Mysql_Field_Data extends Model_Mapper_Scheme_Render_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Render_Abstract
	 */
	public function render (Model_Mapper_Scheme_Entity $entity)
	{
		$result = array ();
		$tmp = array ();
		$data = $entity->getValue ();

		$config = Model_Mapper_Scheme_Render_View_Mysql::config ();
		foreach ($data->all () as $attribute)
		{
			$name = $attribute->getName ();

			$render = Model_Mapper_Scheme_Render::byArgs (
				'Mysql',
				'Field_Attribute',
				$name
			);
			$tmp [$name] = $render->render (
				new Model_Mapper_Scheme_Entity (
					null, $name, $attribute
				)
			);
		}
		$sort = $config->sort;
		if (!$sort)
		{
			return array_values ($tmp);
		}
		$conflict = $config->conflict;
		$exists = array ();
		foreach ($sort as $name)
		{
			if (!isset ($tmp [$name]))
			{
				continue;
			}
			if (
				isset ($conflict->$name) &&
				array_intersect ($conflict->$name->__toArray (), $exists)
			)
			{
				continue;
			}
			$result [$name] = $tmp [$name];
			$exists [] = $name;
		}
		return $result;
	}
}