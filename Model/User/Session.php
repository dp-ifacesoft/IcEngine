<?php

/**
 * Сессия пользователя
 * 
 * @author neon
 * @Orm\Entity(
 *      source="user_session", 
 *      keyGen="Helper_Unique::forModel",
 *      indexes={
 *           {"phpSessionId"},
 *           {"User__id"}
 *      }
 * )
 * @Service("userSession", disableConstruct=true)
 */
class User_Session extends User_Session_Abstract
{
    /**
     * @Orm\Field\Varchar(Size=64, Not_Null)
     * @Orm\Index\Primary
     */
    public $id;
    
    /**
     * @Orm\Field\Varchar(Size=64, Not_Null)
     * @Orm\Index\Key
     */
    public $phpSessionId;
    
    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     * @Orm\Index\Key
     */
    public $User__id;
    
    /**
     * @Orm\Field\Datetime(Not_Null)
     */
    public $lastActive;
    
    /**
     * @Orm\Field\Varchar(Size=32, Not_Null)
     */
    public $remoteIp;
    
    /**
     * @Orm\Field\Varchar(Size=64, Not_Null)
     */
    public $userAgent;
    
    /**
     * @inheritdoc
     */
    public function getParams()
    {
        return array();
    }
}