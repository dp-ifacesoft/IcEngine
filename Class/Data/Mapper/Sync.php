<?php

/**
 * Мэппер для синхронизирующихся моделей
 *
 * @author morph
 */
class Data_Mapper_Sync extends Data_Mapper_Abstract
{
    /**
     * Мэппер для работы с СУДБ
     * 
     * @var Data_Mapper_Abstract
     */
    protected $dynamicMapper;

    /**
     * Мэппер для работы со справочниками
     * 
     * @var Data_Mapper_Abstract
     */
    protected $staticMapper;
    
	/**
	 * Обработчики по видам запросов.
	 *
     * @var array
	 */
	protected $queryMethods = array(
		Query::SELECT	=> '_executeStatic',
		Query::DELETE	=> '_executeDynamic',
		Query::UPDATE	=> '_executeDynamic',
		Query::INSERT	=> '_executeDynamic'
	);

	/**
	 * Запрос на изменение данных (Insert, Update или Delete).
	 *
     * @param Query_Abstract $query Запрос
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function _executeDynamic(Query_Abstract $query, $options)
	{
        $modelName = $this->getModelName($query);
        $this->dynamicMapper->execute($query, $options);
        $this->getService('helperModelSync')->resync($modelName);
	}

	/**
	 * Запрос на выборку
	 *
     * @param Query_Abstract $query Запрос.
	 * @param Query_Options $options Параметры запроса.
	 * @return boolean
	 */
	protected function _executeStatic(Query_Abstract $query, $options)
	{
		$modelName = $this->getModelName($query);
        $rows = $modelName::$rows;
        if (!$rows) {
            $this->getService('helperModelSync')->resync($modelName);
        }
        $result = null;
        $ds = $this->getService('modelScheme')->dataSource($modelName);
        $filters = $modelName::$filters;
        $priorityFields = $modelName::$priorityFields;
        $criterias = $this->getCriterias($query);
        ksort($criterias);
        ksort($filters);
        $criteriasNames = array_keys($criterias); 
        $filtersNames = array_keys($filters);
        if (!$filters && !$priorityFields) {
            $result = $this->staticMapper->execute($ds, $query, $options);
        } elseif ($filters && $criterias && !array_diff($filters, $criterias) && 
            !array_diff($filtersNames, $criteriasNames)) {
            $result = $this->staticMapper->execute($ds, $query, $options);
        } elseif (!array_diff($criteriasNames, $priorityFields)) {
            $result = $this->staticMapper->execute($ds, $query, $options);
            if (!$result->touchedRows()) {
                $result = $this->dynamicMapper->execute($ds, $query, $options);
            }
        } else {
            $result = $this->dynamicMapper->execute($ds, $query, $options);
        }
        return $result->asTable();
	}

	/**
	 * (non-PHPdoc)
     * @inheritdoc
	 * @see Data_Mapper_Abstract::_execute()
	 */
	public function _execute(Query_Abstract $query, $options = null)
	{
		$m = $this->queryMethods[$query->type()];
		$result = $this->{$m}($query, $options);
		return $result;
	}

    /**
     * Получить критерии запроса 
     * 
     * @param Query_Abstract $query
     * @return array
     */
    public function getCriterias($query)
    {
        $criteria = array();
        $where = $query->getPart(Query::WHERE);
        if ($where) {
            foreach ($where as $part) {
                $where = $part[Query::WHERE];
                if (strpos($where, '.') !== false) {
                    list($table, $quotedfield) = explode('.', $where);
                    $field = trim($quotedfield, '`');
                } else {
                    $field = trim($where, '`');
                }
                $criteria[$field] = $part[Query::VALUE];
            }
        }
        return $criteria;
    }
    
    /**
     * Получить мэпер для запросов к СУБД
     * 
     * @return Data_Mapper_Abstract
     */
    public function getDynamicMapper()
    {
        return $this->dynamicMapper;
    }
    
    /**
     * Получить имя модели по запросу
     * 
     * @param Query_Abstract $query
     * @return string
     */
    protected function getModelName($query)
    {
        $from = $query->part(Query::FROM);
        if (!$from) {
            return false;
        }
        $modelName = reset($from)[Query::TABLE];
        return $modelName;
    }
    
    /**
     * Получить сервис по имени
     * 
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
    
    /**
     * Получить мэпер для запросов к справочнику
     * 
     * @return Data_Mapper_Abstract
     */
    public function getStaticMapper()
    {
        return $this->staticMapper;
    }
    
	/**
	 * (non-PHPdoc)
     * @inheritdoc
	 * @see Data_Mapper_Abstract::setOption()
	 */
	public function setOption($key, $value = null)
	{
        $dataMapperManager = $this->getService('dataMapperManager');
		switch ($key) {
			case 'dynamicMapper':
                $dds = $this->getService('dds');
                $sourceConfig = $dds->getDataSource()->getConfig();
                $mapperConfig = isset($sourceConfig['mapper_options'])
                    ? $sourceConfig['mapper_options'] : array();
                $this->dynamicMapper = $dataMapperManager->get(
                    $value, $mapperConfig
                );
				return;
			case 'staticMapper':
				$this->staticMapper = $dataMapperManager->get($value);
				return;
		}
		return parent::setOption($key, $value);
	}
}