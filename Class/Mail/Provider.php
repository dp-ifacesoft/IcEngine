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
     * Получить экземпляр по имени
     *
     * @param string $name
     * @return Mail_Provider_Abstract
     */
    public function byName($name)
	{
		$className = get_class($this) . '_' . $name;
        $provider = new $className;
        return $provider;
	}
}