<?php/** * Менеджер конфигурация * * @author goorus, morph * @Service("configManager") */class Config_Manager{    /**     * Сохраненные конфиги     *     * @var array     */    protected $configs = array();	/**	 * Путь до конфигов от корня сайта	 *     *  @var string	 */	protected $pathToConfig = array('Ice/Config/');        /**     * Провайдер     *      * @var Data_Provider_Abstract     */    protected $provider;	/**	 * Добавляет путь для загрузки конфигураций	 *     * @param string $path	 */	public function addPath($path)	{		$this->pathToConfig[] = $path;	}    /**     * Получить конфиг по рефлексии     *     * @param string $type     * @return array     */    public function byReflection($type)    {        $config = array();        $reflection = new \ReflectionClass($type);        if ($reflection->hasProperty('config')) {            $property = $reflection->getProperty('config');            $property->setAccessible(true);            $config = $property->getValue(                $reflection->newInstanceWithoutConstructor()            );        }        return $config;    }	/**	 * Пустой конфиг.	 *     * @return Config_Array	 */	public function emptyConfig()	{		return new Config_Array(array());	}    /**     * Получить пути до конфигураций     *     * @return type     */	public function getPaths()	{		return $this->pathToConfig;	}	/**	 * Загружает и возвращает конфиг.	 *     * @param string $type Тип конфига.	 * @param string|array $config [optional] Название или конфиг по умолчанию.	 * 		Если параметром $config переданы настройки по умолчанию,	 * 		результатом функции будет смержованный конфиг.	 * @return Objective	 */	public function get($type, $config = '')	{		$resourceKey = $this->getKey($type, $config);        if (isset($this->configs[$resourceKey])) {            return $this->configs[$resourceKey];        }        if (!$config && class_exists($type, false)) {            $config = $this->byReflection($type);        }        $storedConfig = $this->load($type, $config);        if ($storedConfig->asArray()) {            $this->configs[$resourceKey] = $storedConfig;        }		return $storedConfig;	}    /**     * Получить ключ для сохранения конфига     *     * @param string $type     * @param string $config     * @return string     */    public function getKey($type, $config)    {        return 'config:' . $type .            (is_string($config) && $config ? '/' . $config : '');    }	/**	 * Загрузка реального конфига, игнорируя менеджер ресурсов.	 *     * @param string $type Тип конфига.	 * @param string|array $config [optional] Название или конфиг по умолчанию.	 */	public function getReal($type, $config = null)	{		return $this->load($type, $config);	}    /**	 * Загружает конфиг из файла и возвращает класс конфига.	 *     * @param string $type Тип конфига.	 * @param string|array $config Название конфига или конфиг по умолчанию.	 * @return Config_Array|Objective Заруженный конфиг.	 */	protected function load($type, $config = '')	{		$paths = (array) $this->pathToConfig;        $resourceKey = $this->getKey($type, $config);        $filename = $this->provider             ? $this->provider->get($resourceKey) : null;        $fileExists = false;        if (!$filename) {            foreach ($paths as $path) {                $filename = IcEngine::root() . $path.                    str_replace('_', '/', $type) .                    (is_string($config) && $config ? '/' . $config : '') .                    '.php';                if (is_file($filename)) {                    $fileExists = true;                    break;                }            }        }        if ($fileExists && $this->provider) {            $this->provider->set($resourceKey, $filename);        }        $ext = ucfirst(strtolower(substr(strrchr($filename, '.'), 1)));        $className = 'Config_' . $ext;        if ($filename) {            $result = new $className($filename);        } else {            $result = $this->emptyConfig();        }        return is_array($config) ? $result->merge($config) : $result;	}    /**     * Сбросить конфиг     *      * @param strng $type     * @param mixed $config     */    public function reset($type, $config = '')    {		$resourceKey = $this->getKey($type, $config);        $this->configs[$resourceKey] = null;        $this->provider->set($resourceKey, null);    }    	/**	 * Меняет путь до конфига	 *     * @param mixed $path	 */	public function setPathToConfig ($path)	{		$this->pathToConfig = $path;	}        /**     * Изменить провайдер     *      * @param Data_Provider_Abstract $provider     */    public function setProvider($provider)    {        $this->provider = $provider;    }}