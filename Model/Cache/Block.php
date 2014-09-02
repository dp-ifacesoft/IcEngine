<?php

/**
 * Блок кэша
 *
 * @author morph
 * @Orm\Entity
 */
class Cache_Block extends Model
{
    /**
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)
     * @Orm\Index\Primary
     */
    public $id;

    /**
     * @Orm\Field\Varchar(Size=33, Not_Null)
     * @Orm\Index\Key("hash", "action")
     */
    public $hash;

    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     * @Orm\Index\Key("action")
     */
    public $controllerAction;

    /**
     * @Orm\Field\Mediumtext(Not_Null)
     */
    public $json;

    /**
     * @Orm\Field\Datetime(Not_Null)
     */
    public $createdAt;
}