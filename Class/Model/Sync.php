<?php

/**
 * Синхронизирующиеся модели
 * 
 * @author morph
 */
class Model_Sync extends Model
{
    /**
     * Фильтр-флаг для выборки из спровочника
     * 
     * @var array
     */
    public static $filters = array();
    
    /**
     * Поля для выбора с приоритетом из справочника
     * 
     * @var array
     */
    public static $priorityFields = array();
    
    /**
	 * Модели
     * 
     * @param array
	 */
    public static $rows = array();

	/**
	 * (non-PHPDoc)
	 * @see Model::delete
	 */
	public function delete()
	{
		parent::delete();
        $this->getService('helperModelSync')->resync($this->table());
	}

	/**
	 * (non-PHPDoc)
	 * @see Model::save
	 */
	public function save($hardInsert = false)
	{
		parent::save($hardInsert);
        $this->getService('helperModelSync')->resync($this->table());
	}

	/**
	 * (non-PHPDoc)
	 * @see Model::update
	 */
	public function update(array $data, $hardUpdate = false)
	{
		parent::update($data, $hardUpdate);
        $this->getService('helperModelSync')->resync($this->table());
	}
}