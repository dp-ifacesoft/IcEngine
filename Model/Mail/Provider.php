<?php

/**
 * Класс-фабрика провайдера сообщений
 *
 * @author neon
 * @Service("mailProvider")
 */
class Mail_Provider extends Model_Defined
{

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