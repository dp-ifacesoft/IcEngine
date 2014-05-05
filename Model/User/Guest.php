<?php
/**
 *
 * @desc Модель гостя (незарегистрированного посетителя сайта).
 * @author Юрий
 * @package IcEngine
 *
 */
class User_Guest extends User
{

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Конфиг пользователя
		 * @var array
		 */
		'data'	=> array (
			'id'		=> 0,
			'active'	=> 1,
			'login'		=> '',
			'name'		=> 'Гость',
			'email'		=> '',
			'password'	=> ''
		)
	);

	/**
	 * @desc Экзмепляр модели гостя
	 * @var Model
	 */
	protected static $_instance;

	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	protected function _afterConstruct ()
	{
		$this->_loaded = true;
	}

	/**
	 * @desc Создает и возвращает экземпляр модели гостя.
	 * @return User_Guest
	 */
	public static function getInstance ()
	{
		if (!self::$_instance)
		{
			self::$_instance = new self (static::config ()->data->__toArray ());
		}
		return self::$_instance;
	}

	/**
	 * @desc Инициализирует модель гостя.
	 * Модель будет добавлена в менеджер ресурсов.
	 * @param mixed $session_id Идентификатор сессии. Не имеет значения,
	 * параметр необходим для совместимости с User::init ().
	 */
	public static function init ($session_id = null)
	{
		$instance = self::getInstance ();
		Resource_Manager::set ('Model', $instance->resourceKey (), $instance);
	}

	/**
	 * (non-PHPdoc)
	 * @see Model::modelName()
	 */
	public function modelName ()
	{
		return 'User';
	}

}