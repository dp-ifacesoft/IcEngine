<?php

/**
 * Модуль
 * 
 * @author morph
 * @Orm\Entity(source="Defined")
 */
class Module extends Model_Defined
{
    /**
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)
     * @Orm\Index\Primary
     */
    public $id;
    
    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $name;
    
    /**
     * @Orm\Field\Tinyint(Size=1, Not_Null)
     */
    public $active;
    
    /**
     * @Orm\Field\Tinyint(Size=1, Not_Null)
     */
    public $isMain;
    
    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $path;
    
    /**
     * @Orm\Field\Tinyint(Size=1, Not_Null)
     */
    public $hasResource;
    
    /**
     * @inheritdoc
     */
    public static $rows = array(
        array(
            'id'            => 1,
            'name'          => 'Ice',
            'active'        => 1,
            'path'          => '',
            'isMain'        => 1,
            'hasResource'   => 1
        ),
        array(
            'id'            => 2,
            'name'          => 'Admin',
            'active'        => 1,
            'path'          => '',
            'isMain'        => 0,
            'hasResource'   => 1
        )
    );
    
    /**
     * Получить все модули, кроме основного
     * 
     * @return boolean
     */
    public static function notMain()
	{
		$out = array();
		foreach (self::$rows as $row) {
			if (!$row['isMain']) {
				$out[] = $row;
			}
		}
		return $out;
	}

	/**
	 * Получить путь модуля

	 * @return string
	 */
	public function path()
	{ 
        return !empty($this->path) ? $this->path : $this->name;
	}
}