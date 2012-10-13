<?php

/**
 * Соглашение о создании моделей контента (потомков класса Model_Content).
 * Дочерние модели Model_Content должны иметь поля:
 * text		preview		Текстовое превью
 * longtext	text		Содержание
 * text		title		Заголовок
 * int(11)	Content_Category__id 	Раздел контента
 * datetime	date		Время создания
 * int(11)	User__id	Пользователь, разместивший контент
 * int(1)	active		Доступность контента 
 * 
 * @author yury.s
 * @package IcEngine
 *
 */
class Model_Content extends Model
{
    
    /**
     * @return User
     */
    public function user ()
    {
        return $this->User;
    }
    
}