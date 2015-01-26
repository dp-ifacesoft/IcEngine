<?php

/**
 * Хелпер-генератор последовательностей для миграции. В качестве источника 
 * для генерации использует заданый пул источников
 * 
 * @author morph
 * @Service("helperMigrationSequence")
 */
class Helper_Migration_Sequence extends Helper_Abstract
{
    /**
     * Менеждер api
     * 
     * @var Api_Manager
     * @Inject("apiManager")
     */
    protected $apiManager;
    
    /**
     * Хелпер для получение локальной для сервера информации
     * 
     * @var Helper_Site_Location
     * @Inject("helperSiteLocation")
     */
    protected $helperSiteLocation;
    
    /**
     * Пул источников
     * 
     * @var array
     */
    protected $pool = array();
    
    /**
     * Провайдер последовательностей
     * 
     * @var Data_Provider_Abstract
     * @Service(
     *      "dataProviderFile",
     *      args={"File"},
     *      source={
     *          name="dataProviderManager",
     *          method="get"
     *      }
     * )
     */
    protected $provider;
    
    /**
     * Генератор последовательности
     * 
     * @Service(
     *      "migrationSequence",
     *      args={"Increment"},
     *      source={
     *          name="sequenceManager",
     *          method="get"
     *      }
     * )
     * @var Sequence_Abtract
     */
    protected $sequence;
    
    /**
     * Получить следующий элемент последовательности
     * 
     * @return mixed
     */
    public function next()
    {
        if (!$this->pool) {
            $this->pool = $this->helperSiteLocation->get('migration.pool');
        }
        if (!$this->pool) {
            return;
        }
        $apiName = $this->helperSiteLocation->get('migration.api') 
            ?: 'Migration';
        $api = $this->apiManager->get($apiName);
        $nodesToSync = $this->pool;
        $sequence = null;
        foreach ($this->pool as $i => $node) {
            if ($api->status($node) != 'ok') {
                continue;
            }
            unset($nodesToSync[$i]);
            $sequence = $api->next($node);
            break;
        }
        foreach ($nodesToSync as $node) {
            $api->sync($node, $sequence);
        }
        return $sequence;
    }
    
    /**
     * Обработать последовательность
     * 
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function processSequence($key, $value = null)
    {
        if (func_num_args() == 1) {
            $value = $this->provider->get($key);
            $newValue = $this->sequence->next($value);
            $this->processSequence($key, $newValue);
            return $newValue;
        } else {
            $this->provider->set($key, $value);
        }
    }
    
    /**
     * Изменить менеджер api
     * 
     * @param Api_Manager $apiManager
     */
    public function setApiManager($apiManager)
    {
        $this->apiManager = $apiManager;
    }
    
    /**
     * Изменить хелпер для получение локальной информации
     * 
     * @param Helper_Site_Location $helperSiteLocation
     */
    public function setHelperSiteLocation($helperSiteLocation)
    {
        $this->helperSiteLocation = $helperSiteLocation;
    }
    
    /**
     * Изменить генератор последовательностей
     * 
     * @param Sequence_Abstract $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }
}