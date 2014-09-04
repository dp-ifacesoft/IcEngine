<?php

/**
 * Сессии для unitOfWork
 * 
 * @author morph
 * @Service("unitOfWorkSession")
 * @ServiceAccessor
 */
class Unit_Of_Work_Session
{
    /**
     * Драйверы
     * 
     * @var array
     * @Generator
     */
    protected $dataDrivers = array();
    
    /**
     * Сессии
     * 
     * @var array
     * @Generator
     */
    protected $sessions = array();
    
    /**
     * Начать сессию
     * 
     * @param string $modelName
     */
    public function begin($modelName)
    {
        $modelScheme = $this->getService('modelScheme');
        $dataSource = $modelScheme->dataSource($modelName);
        $filters = $dataSource->getFilters();
        $dataFilterManager = $this->getService('dataFilterManager');
        $filter = $dataFilterManager->get('Query_Pool');
        $filter->register($modelName);
        if (!isset($filters[$filter->getName()])) {
            $filters[$filter->getName()] = $filter;
            $dataSource->setFilters($filters);
        }
    }
    
    /**
     * Выполнить запросы сессии
     * 
     * @param string $modelName
     */
    public function flush($modelName)
    {
        $dataFilterManager = $this->getService('dataFilterManager');
        $filter = $dataFilterManager->get('Query_Pool');
        $queries = $filter->getQueries();
        $filter->unregister($modelName);
        if (!isset($queries[$modelName])) {
            return null;
        }
        $unitOfWork = $this->getService('unitOfWork');
        foreach ($queries[$modelName] as $typeQueries) {
            foreach ($typeQueries as $query) {
                $unitOfWork->push($query);
            }
            $unitOfWork->flush();
        }
    }
    
    /**
     * Getter for "dataDrivers"
     *
     * @return array
     */
    public function getDataDrivers()
    {
        return $this->dataDrivers;
    }
        
    /**
     * Setter for "dataDrivers"
     *
     * @param array dataDrivers
     */
    public function setDataDrivers($dataDrivers)
    {
        $this->dataDrivers = $dataDrivers;
    }
    
    
    /**
     * Getter for "sessions"
     *
     * @return array
     */
    public function getSessions()
    {
        return $this->sessions;
    }
        
    /**
     * Setter for "sessions"
     *
     * @param array sessions
     */
    public function setSessions($sessions)
    {
        $this->sessions = $sessions;
    }
    
    /**
     * Get service by name
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        return IcEngine::serviceLocator()->getService($serviceName);
    }
}