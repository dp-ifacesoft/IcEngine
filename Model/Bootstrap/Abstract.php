<?php
/**
 *
 * @desc Абстрактный класс загрузчика
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class Bootstrap_Abstract
{

	/**
	 * @desc Путь до начала структуры Ice.
	 * @var string
	 */
	protected $_basePath;

	/**
	 * @desc Название бутстрапа
	 * @var string
	 */
	protected $_name;

	/**
	 * @desc Флаг выполненного бутстрапа.
	 * @var boolean
	 */
	protected $_runned = false;

	/**
	 * @desc Возвращает загрузчик.
	 * @param string $path путь до этого загрузчика
	 */
	public function __construct ($path)
	{
		$this->_basePath = substr (
			$path,
			0,
			- strlen ('Model_' . get_class ($this) . '.php')
		);
		$this->_name = substr (get_class ($this), strlen ('Bootstrap_'));
	}

	/**
	 * @desc Запускает загрузчик.
	 */
	protected function _run ()
	{
		$this->addLoaderPathes ();

		Loader::multiLoad (
			'Manager_Abstract',
			'Config_Manager',
			'Zend_Exception'
		);

		$this->initFirePhp ();

		Loader::multiLoad (
			'Registry',
			'Request',
			'Executor',
			'Helper_Action',
			'Helper_Date',
			'Helper_Link',
			'Model',
			'Model_Child',
			'Model_Content',
			'Model_Component',
			'Model_Collection',
			'Model_Factory',
			'Model_Factory_Delegate',
			'Model_Option',
			'Component',
			'Controller_Abstract',
			'Controller_Front',
			'Controller_Manager',
			'Page_Title',
			'View_Render',
			'View_Render_Manager',
			'View_Helper_Abstract',
			'Data_Transport_Manager'
		);

		$this->initMessageQueue ();

		$this->initDds ();

		$this->initAttributeManager ();

		$this->initModelScheme ($this->name ());

		$this->initModelManager ();

		$this->initView ();

		$this->initUser ();

		$this->initAcl ();
	}

	/**
	 * @desc Добавление путей в лоадер
	 */
	public function addLoaderPathes ()
	{
		$path = $this->basePath ();

		Loader::addPath ('Class', $path . 'Class/');
		Loader::addPath ('Class', $path . 'Model/');
		Loader::addPath ('Class', $path);

		Loader::addPath ('Controller', IcEngine::path () . 'Controller/');
		Loader::addPath ('Controller', $path . 'Controller/');

		Loader::addPath ('includes', $path . 'includes/');
	}

	/**
	 * @desc Возвращает путь до начала структуры Ice.
	 * @return string.
	 */
	public function basePath ()
	{
		return $this->_basePath;
	}

	/**
	 * @desc Инициализация менеджера атрибутов.
	 */
	public function initAttributeManager ()
	{
		Loader::load ('Attribute_Manager');
		Attribute_Manager::init ();
	}

	/**
	 * @desc Подключение контроля доступа
	 */
	public function initAcl ()
	{
		Loader::multiLoad (
			'Acl_Resource',
			'Acl_Role'
		);
	}

	/**
	 * @desc Инициализация источника данных по умолчанию.
	 */
	public function initDds ($source_name = 'default')
	{
		Loader::multiLoad (
			'Data_Provider_Abstract',
			'Data_Provider_Manager',

			'Query',
			'Query_Options',
			'Query_Result',
			'Query_Translator',

			'DDS',
			'Data_Mapper_Abstract',
			'Data_Source_Abstract',
			'Data_Source_Manager'
		);

		DDS::setDataSource (Data_Source_Manager::get ($source_name));
	}

	/**
	 * @desc Подключение FirePHP
	 */
	public function initFirePhp ()
	{
		if (!function_exists ('fb'))
		{
			Loader::requireOnce ('FirePHPCore/fb.php', 'includes');
		}
	}

	/**
	 * @desc Инициализация очереди событий.
	 */
	public function initMessageQueue ()
	{
		Loader::load ('Message_Queue');

		Message_Queue::flush ();
	}

	/**
	 * @desc Инициализация менеджера моделей и менеджера коллекций.
	 */
	public function initModelManager ()
	{
		Loader::multiLoad (
			'Model_Manager',
			'Model_Collection_Manager'
		);
	}

	/**
	 * @desc Инициализация схемы моделей.
	 * @param string $config
	 */
	public function initModelScheme ($config)
	{
		Loader::load ('Model_Scheme');

		Model_Scheme::init (
			Config_Manager::get ('Model_Scheme', $config)
		);
	}

	/**
	 * @desc Инициализация пользователя и сессии.
	 */
	public function initUser ()
	{
		Loader::multiLoad (
			'User_Abstract',
			'User',
			'User_Guest',
			'User_Session'
		);

		User_Guest::init ();
		User::init ();
	}

	/**
	 * @desc Инициализация рендера.
	 */
	public function initView ()
	{
		View_Render_Manager::getView ();
	}

	/**
	 * @desc Возвращает название загрузчика.
	 * @return string
	 */
	public function name ()
	{
		return $this->_name;
	}

	/**
	 * @desc Запускает загрузчик, если этого не было сделано ранее.
	 */
	public function run ()
	{
		if (!$this->_runned)
		{
			$this->_runned = true;
			$this->_run ();
		}
	}

}