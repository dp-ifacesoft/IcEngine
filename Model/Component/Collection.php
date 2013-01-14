<?php
/**
 * 
 * @desc Абстрактный класс коллекции компонентов.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Component_Collection extends Model_Collection
{ 
	
	/**
	 * @desc Модель, для которой выбрана коллекция.
	 * @var Model
	 */
	protected $_model;
	
	/**
	 * @desc Возвращает первичный ключ модели.
	 * @return mixed
	 */
	public function forRowId ()
	{
	    return $this->_model->key ();
	}
	
	/**
	 * @desc Возвращает таблицу модели
	 * @return string
	 */
	public function forTable ()
	{
	    return $this->_model->table ();
	}
	
	/**
	 * @desc Загрузка списка компонент для записи.
	 * @param Model $model Модель, для которой подгружаются объекты.
	 * @return Component_Collection Эта коллекция.
	 */
	public function getFor (Model $model)
	{
		$this->_model = $model;
		
		$this
		    ->where ('table', $this->_model->table ())
		    ->where ('rowId', $this->_model->key ());
			
		return $this;
	}
	
	/**
	 * @desc Возвращает модель для которой выбрана коллекция.
	 * @return Model Модель.
	 */
	public function model ()
	{
	    return $this->_model;
	}
	
	/**
	 * @desc Привязывает элементы коллекции к заданной сущности.
	 * Существующая ранее связь будет утеряна.
	 * 
	 * @param Model $model
	 * 		Модель, к которой будут привязаны элементы коллекции
	 * @return Component_Collection
	 * 		Эта коллекция
	 */
	public function rejoin (Model $model)
	{
	    $this->_model->component ($this->type (), null);
        $this->_model = $model;
	    
	    $items = &$this->items ();
	    
	    foreach ($items as $item)
	    {
	        /* @var $item Model_Component */
	        $item->rejoin ($this->_model);
//	        update (array (
//	            'table'	=> $this->_model->table (),
//	            'rowId'	=> $this->_model->key ()
//	        ));
	    }
	    
	    if ($this->_model)
	    {
	    	$this->_model->component ($this->type (), $this);
	    }
	    
	    return $this;
	}
	
	/**
	 * @desc Возвращает тип коллекции компонентов.
	 * @return string
	 * 		Имя класса без приставки "Component_" и без суффикса "_Collection"
	 */
	public function type ()
	{
	    return substr (get_class ($this), 10, -11);
	}
	
}