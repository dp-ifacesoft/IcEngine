<?php

/**
 * Менеджер стратегий создания класса
 * 
 * @Service("createClassStrategyManager")
 */
class Create_Class_Strategy_Manager extends Manager_Simple
{
    public $config = [
        'Helper',
        'Class',
        'Service'
    ];
    
    /**
     * Создать класс
     * 
     * @param type $params
     */
    public function create($params)
    {
        $className = $params['name'];
        $type = App::helperClass()->getClassType($className);
        if (in_array($type, $this->config)) {
            $strategy = $this->get($type);
        } else {
            $strategy = $this->get('Class');
        }
        $strategy->create($params);
    }
}