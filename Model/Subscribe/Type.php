<?php

/**
 * Тип рассылки
 *
 * Created at: 2014-10-29 03:07:48
 * @Orm\Entity
 */
class Subscribe_Type extends Model
{
    
    /**
     * id
     *
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)          
     * @Orm\Index\Primary          
     */
    public $id;

    /**
     * Заголовок
     *
     * @Orm\Field\Varchar(Size=255, Not_Null)     
     */
    public $title;

        
}