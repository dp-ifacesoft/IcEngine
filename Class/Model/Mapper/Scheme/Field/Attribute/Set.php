<?php

/**
 * @desc Сет атрибутов поля схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Attribute_Set
{
	/**
	 * @desc Добавленные атрибуты
	 * @var array
	 */
	protected $_set = array ();

	/**
	 * @desc Добавить атрибут в сет
	 * @param Model_Mapper_Sceme_Field_Attribute_Abstract $attribute
	 */
	public function add (Model_Mapper_Scheme_Field_Attribute_Abstract $attribute)
	{
		$this->_set [] = $attribute;
	}

	/**
	 * @desc Получить все атрибуты
	 * @return array
	 */
	public function all ()
	{
		return $this->_set;
	}

	/**
	 * @desc Получить атрибут по имени
	 * @param string $name
	 * @return Model_Scheme_Field_Atribute_Abstract
	 */
	public function byName ($name)
	{
		foreach ($this->_set as $attribute)
		{
			if ($attribute->getName () == $name)
			{
				return $attribute;
			}
		}
	}
}