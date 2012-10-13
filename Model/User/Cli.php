<?php
/**
 *
 * @desc Пользователь для консоли
 * @package IcEngine
 * @author Юрий Шведов
 *
 */
class User_Cli extends User_Abstract
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
			'id'		=> -1,
			'active'	=> 1,
			'login'		=> '',
			'name'		=> '',
			'email'		=> '',
			'password'	=> ''
		)
	);

	/**
	 * @desc Экземпляр пользователя консоли
	 * @var User_Cli
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
	 * @inheritdoc
	 */
	public function hasRole($role)
	{
		return false;
	}

	/**
	 * @desc
	 * @return integer
	 */
	public static function id ()
	{
		return -1;
	}

	public function isAdmin ()
	{
		return true;
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
