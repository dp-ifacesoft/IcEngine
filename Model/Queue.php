<?php

/**
 * 
 *
 * Created at: 2014-10-30 06:01:38
 * @Orm\Entity
 */
class Queue extends Model
{
    
    /**
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)          
     * @Orm\Index\Primary          
     */
    public $id;

    /**
     * Время создания задания
     *
     * @Orm\Field\Datetime(Not_Null)     
     */
    public $createdAt;

    /**
     * Время запуска
     *
     * @Orm\Field\Datetime(Not_Null)     
     */
    public $startTime;

    /**
     * Имя сервиса для выполнения задания
     *
     * @Orm\Field\Varchar(Size=255, Not_Null)     
     */
    public $serviceName;

    /**
     * Имя метода сервиса
     *
     * @Orm\Field\Varchar(Size=50, Not_Null)     
     */
    public $serviceMethod;

    /**
     * Сериализованные параметры
     *
     * @Orm\Field\Longtext(Not_Null)     
     */
    public $serializedParams;

    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     * @Orm\Index\Key
     */
    public $priority;
    
    /**
     * Статус процесса
     *
     * @Orm\Field\Tinyint(Size=4, Not_Null)     
     */
    public $Process_Status__id;

        
}