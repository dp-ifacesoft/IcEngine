<?php
/**
 * 
 * @desc Модель для организации дерева.
 * Имеет родителя и потомков. Для организации связи, в модели должно
 * существовать поле "parentId", определяющее предка.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Model_Child extends Model
{
	/**
	 * @desc Получить коллекцию дочерних категорий
	 * @return Model_Collection
	 */
	public function childs ()
	{
		return Model_Collection_Manager::byQuery (
			$this->modelName (),
			Query::instance ()
				->where (
					'`' . $this->modelName () . '`.parentId',
					$this->key ()
				)
				->where ('`' . $this->modelName () . '`.parentId != 0')
		);
	}
	
	/**
	 * @desc Получить коллекцию дочерних элементов - псевдоним метода childs()
	 * @return Model_Collection
	 **/
	public function children ()
	{
		return $this->childs ();
	}
	
	/**
	 * @desc Возвращает предка.
	 * @return Model_Child
	 */
	public function getParent ()
	{
		return $this->parentKey () ? 
			Model_Manager::byKey ($this->modelName (), $this->parentKey ()) : 
			null;
	} 
	
	/**
	 * 
	 * @param integer|Model $parent
	 * @return boolean
	 */
	public function hasParent ($parent)
	{
		if ($parent instanceof Model)
		{
			if (get_class ($this) != get_class ($parent))
			{
				return false;
			}
			$parent = $parent->key ();
		}
		
		$current = $this->getParent ();
		
		while ($current)
		{
			if ($current->key () == $parent && $parent != 0)
			{
				return true;
			}
			$current = $current->getParent ();
		}
		
		return false;
	}
	
	/**
	 * @desc Возвращает уровень в дереве моделей.
	 * @param integer $rate Множитель. Результат будет домножен на указанную 
	 * величину.
	 * @return integer
	 */
	public function level ($rate = 1)
	{
		if ($this->parentKey () != $this->parentRootKey())
		{
			return ($this->getParent ()->level () + 1) * $rate;
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * @desc Возвращает значение поля с родительским ключом.
	 * @return integer Первичный ключ родителя.
	 */
	public function parentKey ()
	{
		return $this->hasField('parentId') ?
				$this->parentId : 0;
	}
	
	/**
	 * @desc Возврщаает ключ корневого предка.
	 * @return integer 
	 */
	public function parentRootKey ()
	{
		return 0;
	}
	
}