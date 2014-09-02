<?php

/**
 * Менеджер для стратегий спецификаций Specification
 *
 * @author Markov, Apostle
 */
class Manager_Specification extends Manager_Simple
{
    /**
     * @inheritdoc
     */
    protected $config = [];
    
    /**
     * Зарегистрированные спецификации
     * 
     * @var array
     */
    protected $specifications;
    
    /**
     * Инициализация менеджера спецификаций
     */
    public function init()
    {
        $config = $this->config();
        $this->specifications = [];
        if ($config->specifications) {
            foreach ($config->specifications as $specificationName) {
                $this->registerSpecification($specificationName);
            }
        }
    }
    
    /**
     * Зарегистрировать спецификацию
     * 
     * @param string $specificationName
     */
    public function registerSpecification($specificationName)
    {
        $this->specifications[] = $specificationName;
    }
    
    /**
     * Возвращает спецификации
     */
    public function specifications()
    {
        if (is_null($this->specifications)) {
            $this->init();
        }
        $specifications = [];
        foreach ($this->specifications as $name) {
            $specifications[$name] = $this->get($name);
        }
        return $specifications;
    }
}
