<?php

/**
 * Город
 *
 * Created at: 2013-02-02 09:12:11
 * @author neon * @property integer $id
 * @property string $ip IP
 * @property integer $User__id Пользователь
 * @property string $createdAt Создано
 * @property integer $City__id Город
 * @package Vipgeo

 * @category Models
 * @copyright i-complex.ru
 * @Service("authorizationLog")
 * @Orm\Entity
 */
class Authorization_Log extends Model
{
    /**
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)
     * @Orm\Index\Primary
     */
    public $id;
    
    /**
     * @Orm\Field\Varchar(Size=32, Not_Null)
     */
    public $IP;
    
    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     * @Orm\Index\Key
     */
    public $User__id;
    
    /**
     * @Orm\Field\Datetime(Not_Null)
     */
    public $createdAt;
    
    /**
     * Логировать авторизацию
     */
    public function log()
    {
        $user = $this->getService('user')->getCurrent();
        $helperGeoIP = $this->getService('helperGeoIP');
        $city = $helperGeoIP->getCity();
        $modelManager = $this->getService('modelManager');
        $request = $this->getService('request');
        $helperDate = $this->getService('helperDate');
        $log = $modelManager->create('Authorization_Log', array(
            'ip'        => $request->ip(),
            'User__id'  => $user->key(),
            'City__id'  => $city ? $city->key() : 0,
            'createdAt' => $helperDate->toUnix()
        ));
        $log->save();
    }

}