<?php
/**
 * 
 * @desc Модель контента
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Content extends Model_Factory
{
	
	/**
	 * @desc Вовзращает контент по названию модели и ключу.
	 * В случае, если  такой модели не существовало, будет возвращена пустая
	 * модель.
	 * @param string $model_name Название модели.
	 * @param mixed $key Ключ
	 * @return Content_Abstract
	 */
	public static function byName ($model_name, $key)
	{
		$model_name = 'Content_' . ucfirst ($model_name);

		$content = Model_Manager::get (
			$model_name,
			$key
		);

		return $content;
	}
        
        /**
         * @see Model_Factory::delegateClass
         * @param string $model
         * @param string $key
         * @param array|Model $object
         * @return string
         */
        public function delegateClass ($model, $key, $object)
	{
            if (empty ($object ['name']))
            {
                $object ['name'] = 'Simple';
            }
	    return parent::delegateClass ($model, $key, $object);
	}
	
	public function title ()
	{
		return $this->title . ' ' . $this->url;
	}
	
}