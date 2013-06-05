<?php

/**
 * Исполнитель всякого. Необходим для того, чтобы исполнять всякое. (с) morph
 * Предназначени для запуска функций/методов и кэширования результатов
 * их работы.
 * 
 * @author goorus, morph
 * @Service("executor")
 */
class Executor extends Manager_Abstract
{
	/**
	 * Разделитель частей при формировании ключа для кэширования
     * 
	 * @var string
	 */
	const DELIM = '/';

	/**
	 * Кэшер
     * 
	 * @var Data_Provider_Abstract
	 */
	protected $cacher;

	/**
	 * @inheritdoc
	 */
	protected $config = array(
		/**
		 * @desc Провайдер данных, используемый для кэширования по умолчанию
		 * (Data_Provider).
		 * @var string
		 */
		'cache_provider'	=> null,
		/**
		 * @desc Описание кэширования для отдельных функций
		 * @var array
		 */
		'functions'			=> array (
		),
        /**
         * Объекты для логирования
         */
        'forLog'    => array(
            'Controller_Manager'
        ),
        'logProvider'   => 'Controller_Log',
		/**
		 * @desc Провайдер поставки тэгов
		 */
		'tag_provider'		=> null,
		'tags'				=> array()
	);

    /**
	 * Возвращает ключ для кэширования
	 * 
     * @param function $function Кэшируемая функция.
	 * @param array $args Аргкументы функции.
	 * @return string Ключ кэша.
	 */
	protected function getCacheKey($function, array $args)
	{
		$key = $this->getFunctionName($function) . self::DELIM;
		if ($args) {
			$key .= md5(json_encode($args));
		}
		return $key;
	}
    
	/**
	 * Возвращает название функции
     * 
	 * @param string $function Функция
	 * @return string
	 */
	protected function getFunctionName($function)
	{
		if (is_array($function)) {
            $first = $function[0];
			if (is_object ($function[0])) {
                $first = get_class($first);
            }
			return $first . self::DELIM . $function[1];
		} 
        return $function;
	}
    
    /**
	 * Выполняет переданную функцию.
	 * 
     * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @param Objective $options [optional] Опции кэширования.
	 * 		Если не переданы, будут использованы настройки из конфига.
	 * @return mixed Результат выполнения функции.
	 */
	public function execute($function, array $args = array(), $options = null)
	{
		$functionName = $this->getFunctionName($function);
        $config = $this->config();
		if ($options) {
			return $this->executeCaching($function, $args, $options);
		} elseif ($config->functions[$functionName]) {
			$functionOption = $config->functions[$functionName];
            return $this->executeCaching($function, $args, $functionOption);
		}
		return $this->executeUncaching($function, $args);
	}

	/**
	 * Выполнение функции подлежащей кэшированию.
	 * 
     * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @param Objective $options Опции кэширования.
	 * @return mixed Результат выполнения функции.
	 */
	public function executeCaching($function, array $args, $options)
	{
        $cache = array();
        $tagValid = true;
        $expiresValid = true;
        if ($this->getCacher()) {
            $keyFunction = $function;
            if (is_object($keyFunction[0])) {
                $keyFunction[0] = get_class($keyFunction[0]);
            }
            if (isset($options['cacheKey'])) {
                $args[] = call_user_func(array(
                    $this->getService($options['cacheKey'][0]),
                    $options['cacheKey'][1]
                ));
            }
            $key = $this->getCacheKey($keyFunction, $args);
            $cache = $this->getCacher()->get($key);
            $tagValid = $this->isTagValid($cache, $options);
            $expiresValid = $this->isNotExpires($cache, $options);
        }
		$inputValid = $this->isInputValid(
            $options, !empty($args[1]) ? $args[1] : array()
        );
        $functionName = is_object($function[0]) 
            ? get_class($function[0]) : $function[0];
		if ($cache && !$options->forceRecache && $inputValid) {
            if ($tagValid && $expiresValid) {
                if (Tracer::$enabled) {
					if ($functionName == 'Controller_Manager') {
						Tracer::incCachedControllerCount();
					}
				}
				return $cache['v'];
            }
		}
		$start = microtime(true);
		$value = $this->executeUncaching($function, $args);
		$end = microtime(true);
		$delta = $end - $start;
        $config = $this->config();
        $forLog = $config->forLog->__toArray();
		if (in_array($functionName, $forLog)) {
            $this->logFunction($function, $delta, $args);
		}
        if ($this->cacher && $inputValid) {
            $cacheValue = array(
                'v' => $value,
                'a' => time()
            );
            $tags = array();
            if ($options->current_tags) {
                $tags = $options->current_tags->__toArray();
            }
            if ($tags) {
                $cacheValue['t'] = $tags;
            }
            $this->cacher->set($key, $cacheValue);
            if ($cache) {
                $this->cacher->unlock($key);
            }
        }
		return $value;
	}

	/**
	 * Выполнение функции без кэширования.
	 * 
     * @param function $function Функция.
	 * @param array $args Аргументы функции.
	 * @return mixed Результат выполнения функции.
	 */
	public function executeUncaching($function, array $args)
	{
		return call_user_func_array($function, $args);
	}

	/**
	 * Возвращает текущий кэшер.
	 * 
     * @return Data_Provider_Abstract|null
	 */
	public function getCacher()
	{
		if ($this->cacher) {
            return $this->cacher;
        }
        $config = $this->config();
        if ($config->cache_provider) {
            $this->cacher = $this->getService('dataProviderManager')->get(
                $config->cache_provider
            );
        } 
		return $this->cacher;
	}

    /**
     * Проверяет валидны ли данные входного транспорта
     * 
     * @param Objective $options
     * @param array $args
     * @return boolean
     */
    protected function isInputValid($options, $args)
    {
        $inputValid = true;
		if (!$options->inputArgs) {
            return $inputValid;
        }
        foreach ($options->inputArgs as $arg) {
            if (isset($args[$arg])) {
                $inputValid = false;
                break;
            }
        }
        return $inputValid;
    }
    
    /**
     * Не вышел ли срок валидности кэша
     * 
     * @param array $cache
     * @param Objective $options
     * @return boolean
     */
    protected function isNotExpires($cache, $options)
    {
        $expiration = (int) $options->expiration;
        return ($cache['a'] + $expiration > time()) || $expiration == 0;
    }
    
    /**
     * Проверяет валидны ли текущие тэги кэша
     * 
     * @param array $cache
     * @param Objective $options
     * @return boolean
     */
    protected function isTagValid($cache, $options)
    {
        $tagValid = true;
        if (empty($cache['t'])) {
            $tagValid = true;
        } elseif ($options->current_tags) {
            $currentTags = $options->current_tags->__toArray();
            if (array_diff($currentTags, $cache['t'])) {
                $tagValid = false;
            }
        }
        return $tagValid;
    }
    
    /**
     * Логирует вызов функции
     * 
     * @param array $function
     * @param string $delta
     * @param array $args
     */
    protected function logFunction($function, $delta, $args)
    {
        $config = $this->config();
        if (is_object($function[0])) {
            $function[0] = get_class($function[0]);
        }
        $provider = $this->getService('dataProviderManager')->get(
            $config->logProvider
        );
        $logKey = 'log_' . uniqid();
        $provider->set($logKey, array(
            'function'	=> $function,
            'args'		=> $args,
            'delta'		=> $delta,
            'last'		=> time()
        ));
    }
    
	/**
	 * Устаналвивает кэшер
     * 
	 * @param Data_Provider_Abstract $cacher
	 */
	public function setCacher($cacher)
	{
		$this->cacher = $cacher;
	} 
}