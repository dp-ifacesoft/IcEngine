<?php

/**
 * отписавшиеся от рассылки
 *
 * Created at: 2014-10-29 03:08:04
 * @Orm\Entity
 */
class Subscribe_Unsubscriber extends Model
{
    
    /**
     * id
     *
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)          
     * @Orm\Index\Primary          
     */
    public $id;

    /**
     * @Orm\Field\Int(Size=11, Null)          
     * @Orm\Index\Key          
     */
    public $Subscribe_Type__id;

    /**
     * Email или телефон
     *
     * @Orm\Field\Varchar(Size=255, Null)     
     */
    public $identificator;

        
}