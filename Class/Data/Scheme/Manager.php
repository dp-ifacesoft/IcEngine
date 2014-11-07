<?php

/**
 * Менеджер схем данных
 * 
 * @author morph
 * @Service("dataSchemeManager")
 */
class Data_Scheme_Manager extends Manager_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'delegates' => array(
            'Mysqli' => 'Mysql',
            'Sync'   => 'Mysql'
        )
    );
    
    /**
     * Уже созданые делегаты
     * 
     * @var array
     */
    protected $delegates = array();
    
    /**
     * Получить делигата менеджера по имени
     * 
     * @param string $delegateName
     * @return Data_Scheme_Manager_Delegate_Abstract
     */
    public function getDelegate($delegateName)
    {
        if (isset($this->delegates[$delegateName])) {
            return $this->delegates[$delegateName];
        }
        $className = 'Data_Scheme_Manager_Delegate_' . $delegateName;
        $delegate = new $className;
        $this->delegates[$delegateName] = $delegate;
        return $delegate;
    }
    
    /**
     * Наполнить схему данных
     * 
     * @param Data_Scheme $dataScheme
     * @return Data_Scheme
     */
    public function getScheme($dataScheme)
    {
        $config = $this->config();
        $delegates = $config->delegates;
        if (!$delegates) {
            return $dataScheme;
        }
        $dataDriver = $dataScheme->getDataSource()->driver();
        $driverParents = class_parents(get_class($dataDriver));
        foreach ($delegates as $parentName => $delegateName) {
            $fullParentName = 'Data_Driver_' . $parentName;
            if (!isset($driverParents[$fullParentName])) {
                continue;
            }
            return $this->getDelegate($delegateName)->getScheme($dataScheme);
        }
        return $dataScheme;
    }
}
