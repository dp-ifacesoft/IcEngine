<?php

/**
 * Класс-фабрика провайдера сообщений
 *
 * @author neon
 * @Service("mailProvider")
 * @Orm\Entity(source="Defined")
 */
class Mail_Provider extends Model_Defined
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
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $title;
    
    /**
     * @Orm\Field\Tinyint(Size=1, Not_Null)
     */
    public $active;
    
    /**
     * @inheritdoc
     * @var array
     */
    public static $rows = array(
        array(
            'id'     => 1,
            'name'   => 'Mimemail',
            'title'  => 'Отправка на почту',
            'active' => 1
        ),
        array(
            'id'     => 2,
            'name'   => 'Sms_Dcnk',
            'title'  => 'Смс через dc-nk.ru',
            'active' => 1
        ),
        array(
            'id'     => 3,
            'name'   => 'Sms_Littlesms',
            'title'  => 'Смс через littlesms',
            'active' => 1
        ),
        array(
            'id'     => 4,
            'name'   => 'Sms_Yakoon',
            'title'  => 'Смс через yakoon',
            'active' => 1
        ),
        array(
            'id'     => 5,
            'name'   => 'First_Success',
            'title'  => 'Отправка до первого успешного',
            'active' => 1
        )
    );

}