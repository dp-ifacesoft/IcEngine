<?php

/**
 * Загрузчик модулей и классов.
 *
 * @author goorus, morph
 * @Service("loader")
 */
class Loader
{
    /**
     * Перегруженные классы
     * 
     * @var array
     */
    protected $overrides;
    
	/**
	 * Пути.
	 *
     * @var array
	 */
	protected $paths = array();

    /**
     * Провайдер для загрузчика
     *
     * @var Data_Provider_Abstract
     */
    protected $provider;

	/**
	 * Подключенные классы
	 *
     * @var array
	 */
	protected $required = array();

	/**
	 * Добавленные пути
	 *
     * @param string $type Тип.
	 * @param string $path Путь.
	 */
	public function addPath($type, $path)
	{
        if (!isset($this->paths[$type])) {
            $this->paths[$type] = array();
        }
        $this->paths[$type][] = $path;
	}

	/**
	 * Добавление путей
     *
	 * @param array $paths
	 */
	public function addPathes(array $paths)
	{
		foreach ($paths as $type => $typePaths) {
            foreach ((array) $typePaths as $path) {
                $this->addPath($type, $path);
            }
		}
	}

	/**
	 * Возвращает полный путь до файла. Если файла не существует, возвращается
     * false.
	 *
     * @param string $file Искомый файл.
	 * @param string $type Тип.
	 * @return string Если файл найден, полный путь до файла. Иначе false.
	 */
	public function findFile($file, $type = 'Class')
	{
        if (!isset($this->paths[$type])) {
            return false;
        }
        $overrides = $this->getOverrides();
        if (isset($overrides[$file])) {
            return IcEngine::root() . $overrides[$file];
        }
		foreach (array_reverse($this->paths[$type]) as $path) {
			$filename = $path . $file;
			if (file_exists($filename)) {
				return $filename;
			}
		}
		return false;
	}

    /**
     * Получить перегруженные пути
     * 
     * @return array
     */
    public function getOverrides()
    {
        if (is_null($this->overrides)) {
            $filename = IcEngine::root() . 'Ice/Config/Loader/Override.php';
            if (is_file($filename)) {
                $overrides = include($filename);
                $this->overrides = $overrides;
            }
        }
        if (is_null($this->overrides)) {
            $this->overrides = array();
        }
        return $this->overrides;
    }
    
	/**
	 * Возвращает все пути для указанного типа.
	 *
     * @param string $type
	 * @return array
	 */
	public function getPaths($type)
	{
        $pathes = isset($this->paths[$type]) ? $this->paths[$type] : array();
		return $pathes;
	}

    /**
     * Получить провайдера для загрузчика
     *
     * @return Data_Provider_Abstract
     */
    public function getProvider()
    {
        return $this->provider;
    }

	/**
	 * Проверяет был ли уже подключен файл
	 *
     * @param string $file
	 * @param string $type
	 * @return bool
	 */
	public function getRequired($file, $type)
	{
        if (!isset($this->required[$type])) {
            return false;
        }
		return isset($this->required[$type][$file]);
	}

    /**
	 * Подключение класса.
	 *
     * @param string $class_name Название класса.
	 * @param string $type [optional]
	 * @return boolean true, если удалось подключить, иначе false.
	 */
	public function load($class, $type = 'Class')
	{
		if (class_exists($class, false)) {
			return true;
		}
        $filename = str_replace('_', '/', $class) . '.php';
		return $this->requireOnce($filename, $type);
	}

	/**
	 * Подключение файла.
	 *
     * @param string $file
	 * @param string $type
     * @param boolean $exceptionThrow
	 * @return boolean
	 */
	public function requireOnce($file, $type, $exceptionThrow = true)
	{
		if (isset($this->required[$type], $this->required[$type][$file])) {
			return true;
		}
        if (!isset($this->paths[$type])) {
            return false;
        }
        if ($this->provider) {
            $key = $type . '/' . $file;
            $filename = $this->provider->get($key);
            if (is_bool($filename)) {
                $filename = $this->findFile($file, $type);
                $this->provider->set($key, $filename ?: null);
            }
        } else {
            $filename = $this->findFile($file, $type);
        }
        if ($filename) {
            if (!isset($this->required[$type])) {
                $this->required[$type] = array();
            }
            $this->required[$type][$file] = true;
            require_once $filename;
            if (class_exists('Tracer', false) && Tracer::$enabled) {
                Tracer::incLoadedClassCount();
            }
            return true;
        }
        $autoloaders = spl_autoload_functions();
        if (count($autoloaders) > 1) {
            $exceptionThrow = false;
        }
        if ($exceptionThrow) {
            throw new Exception('Class ' . $file . ' not found');
        }
	}

    /**
     * Изменить перегруженные классы
     * 
     * @param array $overrides
     */
    public function setOverrides($overrides)
    {
        $this->overrides = $overrides;
    }
    
	/**
	 * Заного устанавливает пути до файлов. Предыдущие пути будут удалены.
	 *
     * @param string $type Тип.
	 * @param string|array $path Путь или массив патей.
	 */
	public function setPath($type, $path)
	{
		self::$paths[$type] = (array) $path;
	}

    /**
     * Изменить провайдер
     *
     * @param Data_Provider_Abstract $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

	/**
	 * Делает отметку о подключении файла.
	 *
     * @param string $file Файл.
	 * @param string $type Тип.
	 * @param boolean $required [optional]
	 */
	public function setRequired($file, $type, $required = true)
	{
        if (!isset($this->required[$type])) {
            $this->required[$type] = array();
        }
		$this->required[$type][$file] = $required ? true : null;
	}

	/**
	 * Попытка подключить указанный класс. В случае ошибки не возникает
     * исключения.
	 *
     * @param string $class Название класса.
	 * @param string $type [optional]
	 * @return boolean true в случае, если файл класса подключен или класс
	 * уже подключен, иначе false.
	 */
	public function tryLoad($class, $type = 'Class')
	{
		if (class_exists($class, false)) {
			return true;
		}
        $filename = str_replace('_', '/', $class) . '.php';
        return $this->requireOnce($filename, $type, false);
	}
}