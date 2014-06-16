<?php

/**
 * Хелпер для пометки миграций в случае если необходимо, чтобы миграция
 * выполнилась только один раз для конкретного источника данных
 * 
 * @author morph
 * @Service("helperMigrationMark")
 */
class Helper_Migration_Mark extends Helper_Abstract
{
    /**
     * Api для сохранения статуса миграций
     * 
     * @var Api_Scheme_Abstract
     * @Inject(
     *      "migrationMarkApi",
     *      args={"Migration_Mark"},
     *      source={
     *          name="apiManager",
     *          method="get"
     *      }
     * ) 
     */
    protected $api;
    
    /**
     * Хелпер межсайтовых значений
     * 
     * @var Helper_Site_Location
     * @Inject("helperSiteLocation")
     */
    protected $helperSiteLocation;
    
    /**
     * Получить url api маркировки миграций
     * 
     * @return string
     */
    protected function getApiUrl()
    {
        return $this->helperSiteLocation->get('migration.markApiUrl');
    }
    
    /**
     * Получить конфигурацию для помеченных миграций
     */
    protected function getConfig()
    {
        $config = $this->getService('configManager')->get('Migration_Mark');
        if (!$config) {
            $this->rewriteConfig(array());
            return array();
        }
        return $config->__toArray();
    }
    
    /**
     * Помечена ли миграция
     * 
     * @param string $migrationName
     * @param string $locationName
     * @return boolean
     */
    public function isMarked($migrationName, $locationName = null)
    {
        $config = $this->getConfig();
        $apiUrl = $this->getApiUrl();
        if (!$locationName) {
            $locationName = $this->helperSiteLocation->getLocation();
        }
        if ($apiUrl) {
            $this->api->setParam('url', $apiUrl);
            return $this->api->get($locationName, $migrationName);
        } else {
            return isset(
                $config[$locationName], $config[$locationName][$migrationName]
            );
        }
    }
    
    /**
     * Пометить миграцию
     * 
     * @param string $migrationName
     * @param string $locationName
     */
    public function mark($migrationName, $locationName = null)
    {
        $apiUrl = $this->getApiUrl();
        if (!$locationName) {
            $locationName = $this->helperSiteLocation->getLocation();
        }
        if ($apiUrl) {
            $this->api->setParam('url', $apiUrl);
            $this->api->set($locationName, $migrationName);
        } else {
            $config = $this->getConfig();
            if (!isset($config[$locationName])) {
                $config[$locationName] = array();
            }
            $config[$locationName][$migrationName] = true;
            $this->rewriteConfig($config);
        }
    }
    
    /**
     * Перезаписать конфигурацию пометок
     * 
     * @param array $config
     */
    protected function rewriteConfig($config)
    {
        $filename = IcEngine::root() . 'Ice/Config/Migration/Mark.php';
        $output = $this->getService('helperCodeGenerator')->fromTemplate(
            'migrationMark', array('config' => $config)
        );
        file_put_contents($filename, $output);
    }
    
    /**
     * Изменить api
     * 
     * @param Api_Scheme_Abstract $api
     */
    public function setApi($api)
    {
        $this->api = $api;
    }
    
    /**
     * Изменить сервис межсайтовых значений
     * 
     * @param Helper_Site_Location $helperSiteLocation
     */
    public function setHelperSiteLocation($helperSiteLocation)
    {
        $this->helperSiteLocation = $helperSiteLocation;
    }
}