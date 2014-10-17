<?php
/**
 * Метод авторизации пользователя.
 * Фабрика для моделей авторизации. Такое разделение позволяет
 * для каждого пользователя реализовать несколько методов авторизации
 * (по логин, по емейлу, по телефону и т.п.)
 *
 * @author goorus, morph
 * @Orm\Entity(source="Defined")
 * @Orm\Profile("default")
 * @Service("authorization")
 */
class Authorization extends Model_Defined
{
    /**
     * @Orm\Field\Int(Auto_Increment)
     * @Orm\Index\Key
     */
    public $id;
    
    /**
     * @Orm\Field\Varchar
     */
    public $name;
    
    /**
     * @inheritdoc
     */
    public static $rows = array(
        array(
            'id'    => 1,  
            'name'  => 'Login_Password_Sms',
        )
    );
    
	/**
	 *  Возвращает модель авторизации по названию
	 *
	 * @param string $name Название модели авторизации
	 * @return Authorization_Abstract
	 */
	public function byName($name)
	{
		return $this->getService('modelManager')->byOptions(
			'Authorization'
		)->addOptions(array(
            'name'  => '::Name',
            'value' => $name
        ));
	}
}