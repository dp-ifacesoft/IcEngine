<?php

/**
 * Задание front-контроллера
 * 
 * @author morph
 */
class Controller_Front_Task extends Controller_Task
{
    /**
     * Действия, измененые для задачи фронт контроллера
     * 
     * @var array
     */
    protected $actions;
    
    /**
     * Выходной транспорт
     * 
     * @var Data_Transport
     */
    protected $output;
    
    /**
     * Текущий роут 
     * 
     * @var Route
     */
    protected $route;
    
    /**
     * Стратегии 
     * 
     * @var array
     */
    protected $strategies;
    
    /**
     * Текущая стратегия
     * 
     * @var Controller_Front_Strategy_Abstract
     */
    protected $strategy;
    
    /**
     * Менеджер стратегии
     * 
     * @var Controller_Front_Strategy_Manager
     */
    protected $strategyManager;
    
    /**
     * Получить действия фронт-контроллера
     * 
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }
    
    /**
     * Получить выходной транспорт
     * 
     * @return Data_Transport
     */
    public function getOutput()
    {
        return $this->output;
    }
    
    /**
     * Получить роут
     * 
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
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
     * Получить стратегии
     * 
     * @return array
     */
    public function getStrategies()
    {
        return $this->strategies;
    }
    
    /**
     * Получить стратегию
     * 
     * @return Controller_Font_Strategy_Abstract
     */
    public function getStrategy()
    {
        if ($this->strategy) {
            return $this->strategy;
        }
        $strategyManager = $this->getStrategyManager();
        $strategies = $this->getStrategies();
        foreach ($strategies as $strategyName) {
            $strategy = $strategyManager->get($strategyName);
            if ($strategy->isSatisfiedBy($this)) {
                $this->strategy = $strategy;
                return $strategy;
            }    
        }
    }
    
    /**
     * Получить менеджера стратегий
     * 
     * @return Controller_Front_Strategy_Manager
     */
    public function getStrategyManager()
    {
        if (is_null($this->strategyManager)) {
            $this->strategyManager = new Controller_Front_Strategy_Manager();
        }
        return $this->strategyManager;
    }
    
    /**
     * Изменить действия фронт-контроллера
     * 
     * @param array $actions
     */
    public function setActions($actions)
    {
        $this->actions = $actions;
    }
    
    /**
     * Изменить выходной транспорт
     * 
     * @param Data_Transport $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }
    
    /**
     * Изменить роут
     * 
     * @param Route $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }
    
    /**
     * Изменить стратегии
     * 
     * @param array $strategies
     */
    public function setStrategies($strategies)
    {
        $this->strategies = $strategies;
    }
    
    /**
     * Изменить текущую стратегию
     * 
     * @param Controller_Front_Strategy_Abstract $strategy
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }
    
    /**
     * Изменить менеджер стратегий
     * 
     * @param Controller_Front_Strategy_Manager $strategyManager
     */
    public function setStrategyManager($strategyManager)
    {
        $this->strategyManager = $strategyManager;
    }
}