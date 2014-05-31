<?php

/**
 * Драйвер для работы с Mongodb, с кэшированием запросов
 *
 * @author goorus, morph
 */
class Data_Driver_Mongo_Cached extends Data_Driver_Mongo
{
	/**
	 * Кэшер запросов
     *
	 * @var Data_Provider_Abstract
	 */
	protected $cacher;

	/**
	 * @inheritdoc
	 */
	protected function executeDelete(Query_Abstract $query, 
        Query_Options $options)
	{
        $this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		parent::executeDelete($query, $options);
		$tags = $query->getTags();
		for ($i = 0, $count = sizeof($tags); $i < $count; ++$i) {
			$this->cacher->tagDelete($tags[$i]);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function executeInsert(Query_Abstract $query, 
        Query_Options $options)
	{
        $this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		parent::executeInsert($query, $options);
		$tags = $query->getTags();
		for ($i = 0, $count = sizeof($tags); $i < $count; ++$i) {
			$this->cacher->tagDelete($tags [$i]);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function executeSelect(Query_Abstract $query, 
        Query_Options $options)
	{
		$key = $this->queryHash();
		$expiration = $options->getExpiration();
		$cache = $this->cacher->get($key);
		$useCache = false;
		if ($cache) {
            if ($cache['a'] + $expiration > time() || $expiration == 0) {
                if ($this->cacher->checkTags($cache['t'])) {
                    $useCache = true;
                }
            }
		}
		if ($useCache) {
			$this->foundRows = $cache['f'];
			$this->result = $cache['v'];
            return;
		}
        $this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		parent::executeSelect($query, $options);
		$tags = $query->getTags();
		$this->cacher->set(
			$key,
			array(
				'v' => $this->result,
				'a' => time(),
				't' => $this->cacher->getTags($tags),
				'f'	=> $this->foundRows
			)
		);
	}

    /**
     * @inheritdoc
     */
    protected function executeShow(Query_Abstract $query, Query_Options $options) 
    {
        $this->connect();
        parent::executeShow($query, $options);
    }

	/**
	 * @inheritdoc
	 */
	protected function executeUpdate(Query_Abstract $query, 
        Query_Options $options)
	{
        $this->collection = $this->connect()->selectCollection(
			$this->connectionOptions['database'],
			$this->query['collection']
		);
		parent::_executeUpdate($query, $options);
		$tags = $query->getTags();
		for ($i = 0, $count = sizeof($tags); $i < $count; ++$i) {
			$this->cacher->tagDelete($tags[$i]);
		}
	}

    /**
     * @inheritdoc
     */
	public function execute(Query_Abstract $query, $options = null)
	{
		if (!($query instanceof Query_Abstract)) {
			return new Query_Result(null);
		}
		$start = microtime (true);
		$this->query = $query->translate('Mongo');
		$this->result = array();
		$this->touchedRows = 0;
		$this->foundRows = 0;
		$this->insertId = null;
		if (!$options) {
			$options = $this->getDefaultOptions();
		}
		$this->callMethod($query, $options);
		$finish = microtime (true);
		return new Query_Result(array(
			'error'			=> '',
			'errno'			=> 0,
			'query'			=> $query,
			'startAt'		=> $start,
			'finishedAt'	=> $finish,
			'foundRows'		=> $this->foundRows,
			'result'		=> $this->result,
			'touchedRows'	=> $this->touchedRows,
			'insertKey'		=> $this->insertId
		));
	}

	/**
     * Получить кэшер запросов
     *
	 * @return Data_Provider_Abstract
	 */
	public function getCacher()
	{
		return $this->cacher;
	}
    
    /**
	 * Получение хэша запроса
     *
	 * @return string
	 */
	protected function queryHash()
	{
		return md5(json_encode($this->query));
	}

	/**
	 * Изменить кэшер запросов
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
				$this->setCacher($dataProviderManager->get($value));
				return;
			case 'expiration':
				$this->getDefaultOptions()->setExpiration($value);
				return;
		}
		return parent::setOption($key, $value);
	}
}