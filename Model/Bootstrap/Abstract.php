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
		$this->initFirePhp ();
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
		Loader::addPath('Controller', $path . 'Controller/');
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
		Attribute_Manager::init ();
	}

	/**
	 * @desc Подключение контроля доступа
	 */
	public function initAcl ()
	{

	}

	/**
	 * @desc Инициализация источника данных по умолчанию.
	 */
	public function initDds ($source_name = 'default')
	{
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

	}

	/**
	 * @desc Инициализация менеджера моделей и менеджера коллекций.
	 */
	public function initModelManager ()
	{

	}

	/**
	 * @desc Инициализация схемы моделей.
	 * @param string $config
	 */
	public function initModelScheme ($config)
	{
		Model_Scheme::init (
			Config_Manager::get ('Model_Scheme', $config)
		);
	}

	/**
	 * @desc Инициализация пользователя и сессии.
	 */
	public function initUser ()
	{
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
