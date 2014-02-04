<?php

/**
 * Задачи планировщика
 * 
 * @author morph
 * @Orm\Entity
 */
class Schedule extends Model
{
    /**
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)
     * @Orm\Index\Primary
     */
    public $id;
    
    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $controllerAction;
    
    /**
     * @Orm\Field\Varchar(Size=255, Not_Null)
     */
    public $paramsJson;
    
    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     */
    public $deltaSec;
    
    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     */
    public $lastTs;
    
    /**
     * @Orm\Datetime(Not_Null)
     */
    public $lastDate;
    
    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     * @Orm\Index\Key
     */
    public $priority;
    
    /**
     * @Orm\Field\Tinyint(Size=1, Not_Null)
     */
    public $inProcess;
}