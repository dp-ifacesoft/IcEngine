<?php

/**
 * Драйвер для работы с mysql, с кэшированием запросов.
 *
 * @author goorus, morph, neon
 */
class Data_Driver_Mysqli_Cached extends Data_Driver_Mysqli
{
	/**
	 * Кэшер запросов.
	 *
     * @var Data_Provider_Abstract
	 */
	protected $cacher;

    /**
     * Кэши, уже полученные из провайдера
     *
     * @var array
     */
    protected static $caches = array();

    /**
     * Кэш запросов по тэгам
     *
     * @var array
     */
    protected static $tagsCaches = array();

    /**
     * @inheritdoc
     */
    public function callMethod($query, $options)
    {
        $method = $this->queryMethods[$query->type()];
        if ($method != 'executeSelect') {
            $this->sql = $query->translate('Mysql');
        }

        return parent::callMethod($query, $options);
    }

    /**
     * Очистка кэша драйвера
     */
    public function clearCache()
    {
        self::$caches = array();
        self::$tagsCaches = array();
    }

	/**
	 * @inheritdoc
	 */
	protected function executeChange(Query_Abstract $query,
        Query_Options $options)
	{
		if (!$this->handler) {
			$this->connect();
		}
		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}
        $result = parent::executeChange($query, $options);
        if (!$result) {
            return false;
        }
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			if ($query instanceof Query_Delete) {
				Tracer::incDeleteQueryCount();
				Tracer::incDeleteQueryTime($delta);
			} else {
				Tracer::incUpdateQueryCount();
				Tracer::incUpdateQueryTime($delta);
			}
			Tracer::incDeltaQueryCount();
		}
		if ($this->affectedRows > 0) {
			$tags = $query->getTags();
			for ($i = 0, $count = sizeof($tags); $i < $count; ++$i) {
                $tag = $tags[$i];
				$this->cacher->tagDelete($tag);
                $this->tagDelete($tag);
			}
		}
		return true;
	}

	/**
	 * @inheritdoc
	 */
	protected function executeInsert(Query_Abstract $query,
        Query_Options $options)
	{
		if (!$this->handler) {
			$this->connect();
		}
		if (Tracer::$enabled) {
			$startTime = microtime(true);
		}
		$result = parent::executeInsert($query, $options);
        if (!$result) {
            return false;
        }
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			Tracer::incUpdateQueryCount();
			Tracer::incUpdateQueryTime($delta);
			Tracer::incDeltaQueryCount();
		}
		if ($this->affectedRows > 0) {
			$tags = $query->getTags();
			for ($i = 0, $count = sizeof($tags); $i < $count; $i++) {
				$tag = $tags[$i];
                $this->cacher->tagDelete($tag);
                $this->tagDelete($tag);
			}
		}
		return true;
	}

	/**
	 * Выполняет запрос на получение данных.
	 *
     * @param Query_Abstract $query
	 * @param Query_Options $options
	 * @return null|array
	 */
	protected function executeSelect(Query_Abstract $query,
        Query_Options $options)
	{
		if (Tracer::$enabled) {
			Tracer::incSelectQueryCount();
            Tracer::appendQueryToVector($query->translate('Mysql'));
		}
		$key = $this->sqlHash($query);
		$expiration = $options->getExpiration();
        $fromCache = false;
        if (!isset(self::$caches[$key])) {
            $cache = $this->cacher->get($key);
        } else {
            $cache = self::$caches[$key];
            $fromCache = true;
        }
		$cacheValid = false;
		if ($cache) {
            $tagsValid = $fromCache ?: $this->isTagsValid($cache['t']);
            $expiresValid = $cache['a'] + $expiration > time() || !$expiration;
			$cacheValid = $expiresValid && $tagsValid;
		}
		if ($cacheValid) {
            self::$caches[$key] = $cache;
			$this->numRows = count($cache['v']);
			$this->foundRows = $cache['f'];
			if (Tracer::$enabled) {
				Tracer::incCachedSelectQueryCount();
			}
			$rows = $cache['v'];
		} else {
            $rows = $this->getRows($query, $options);
            $this->numRows = count($rows);
        }
        return $rows;
	}

	/**
	 * @inheritdoc
	 */
	public function execute(Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		$start = microtime(true);
		$this->errno = 0;
		$this->error = '';
		$this->affectedRows = 0;
		$this->foundRows = 0;
		$this->numRows = 0;
		$this->insertId = null;
		if (!$options) {
			$options = $this->getDefaultOptions();
		}
		$result = $this->callMethod($query, $options);
        if ($this->errno) {
			throw new Exception($this->error . "\n" . $this->sql, $this->errno);
		}
		if (!$this->errno && is_null($result)) {
			$result = array();
		}
		$finish = microtime(true);
		$queryResult = new Query_Result(array(
			'error'			=> $this->error,
			'errno'			=> $this->errno,
			'query'			=> $query,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'foundRows'		=> $this->foundRows,
            'numRows'       => $this->numRows,
			'result'		=> $result,
			'touchedRows'	=> $this->touchedRows ?:
                $this->numRows + $this->affectedRows,
			'insertKey'		=> $this->insertId
		));
        return $queryResult;
	}

	/**
     * Получить текущего кэшера
     *
	 * @return Data_Provider_Abstract
	 */
	public function getCacher()
	{
		return $this->cacher;
	}

    /**
     * Вернуть ряды из источника
     *
     * @param Query_Abstract $query
     * @param Query_Options $options
     * @return array
     */
    protected function getRows($query, $options)
    {
        if (!$this->handler) {
			$this->connect();
		}
		if (Tracer::$enabled) {
			$startTime = microtime(true);
			Tracer::begin(__CLASS__, __METHOD__, __LINE__);
		}
        $key = $this->sqlHash($query);
        $this->sql = $query->translate('Mysql');
		$rows = parent::executeSelect($query, $options);
		if (Tracer::$enabled) {
			$endTime = microtime(true);
			$delta = $endTime - $startTime;
			if ($delta >= Tracer::LOW_QUERY_TIME) {
				Tracer::addLowQuery($this->sql, $delta);
			} else {
				Tracer::incSelectQueryTime($delta);
			}
			Tracer::end(
                $this->sql,
                $this->numRows,
				memory_get_usage()
            );
			Tracer::incDeltaQueryCount();
		}
        if (is_null($rows)) {
            return null;
        }
		$tags = $query->getTags();
        $providerTags = $this->cacher->getTags($tags);
        if ($tags) {
            foreach (array_keys($providerTags) as $tag) {
                self::$tagsCaches[$tag][] = $key;
            }
        }
        $cache = array (
            'v' => $rows,
            'a' => time(),
            't' => $providerTags,
            'f'	=> $this->foundRows
        );
        self::$caches[$key] = $cache;
		$this->cacher->set($key, $cache);
		return $rows;
    }

    /**
     * Проверяет валидны ли тэги
     *
     * @param array $tags
     * @return boolean
     */
    protected function isTagsValid($tags)
    {
        return $this->cacher->checkTags($tags);
    }

    /**
	 * Изменить текущего кэшера
     *
	 * @param Data_Provider_Abstract $cacher
	 */
	public function setCacher(Data_Provider_Abstract $cacher)
	{
		$this->cacher = $cacher;
	}

	/**
	 * @inheritdoc
	 */
	public function setOption($key, $value = null)
	{
		switch ($key) {
			case 'cacher':
                $serviceLocator = IcEngine::serviceLocator();
                $dataProviderManager = $serviceLocator->getService(
                    'dataProviderManager'
                );
                $provider = $dataProviderManager->get($value);
				$this->setCacher($provider);
				return;
			case 'expiration':
				$this->getDefaultOptions()->setExpiration($value);
				return;
		}
		return parent::setOption($key, $value);
	}

    /**
	 * Получение хэша запроса
	 *
     * @return string
	 */
	protected function sqlHash($query)
	{
		return md5(serialize($query->getParts()));
	}

    /**
     * Удаляет внутренние сохраненные тэги и запросы
     *
     * @param string $tag
     */
    public function tagDelete($tag)
    {
        foreach (self::$tagsCaches as $tag => $keys) {
            unset(self::$tagsCaches[$tag]);
            foreach ($keys as $key) {
                if (!isset(self::$caches[$key])) {
                    continue;
                }
                unset(self::$caches[$key]);
            }
        }
    }
}