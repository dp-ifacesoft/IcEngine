<?php

/**
 * Помощник для консоли 
 *
 * @author Apostle
 * @Service("helperCli")
 */
class Helper_Cli extends Helper_Abstract
{
    /**
     *
     * @var string тип индикатора ('/','.') 
     */
    private $_indicatorType = '';
    /**
     *
     * @var integer текущий индикатор 
     */
    private $_currentIndicatorIterator = 0;
    /**
     *
     * @var mixed последовтельность для каждого индикатора
     */
    protected $config = [
        'default'   =>  '/',
        '/' =>  ['-', '\\', '|', '/'],
        '.' =>  ['.', '..', '...', '....', '.....', '....', '...', '..']
    ];
    
    /**
     * крутилочка для визуального отбражения в консоли:)
     * @return string
     */
    public function next()
     {
        $indicatorCollection = $this->config[$this->getIndicatorType()];
        if ($this->getCurrentIndicator()+1 == count($indicatorCollection)) {
            $this->setCurrentIndicator(-1);
        }
        $indicator = $this->getCurrentIndicator();
        $this->setCurrentIndicator(++$indicator);
        return $indicatorCollection[$indicator];
    }
    
    /**
     * вывести
     * @param string $text текст для отображения
     */
    public function say($text ='')
    {
        if (!$this->getIndicatorType()) {
            $this->setIndicatorType($this->config['default']);
        }
        $next = $this->next();
        $nextLength = strlen($next);
        if ($text) {
            echo str_repeat("\r", $nextLength) . $text . $next 
                . str_repeat(' ', $nextLength);
        } else {
            echo $next . str_repeat(chr(8), $nextLength) 
                . str_repeat(' ', $nextLength);
        }
    }
    
    /**
     * Установить индикатор
     * @param type $indicator
     */
    public function setIndicatorType($indicator) 
    {
        $this->_indicatorType = $indicator;
    }
    
    /**
     * Установить индикатор
     * @param type $indicator
     */
    public function getIndicatorType() 
    {
        return $this->_indicatorType;
    }
    
    /**
     * установить текущий индикатор
     * @param integer $value
     */
    public function setCurrentIndicator($value)
    {
        $this->_currentIndicatorIterator = $value;
    }
    
    /**
     * получить текущий итератор
     * @return integer
     */
    public function getCurrentIndicator()
    {
        return $this->_currentIndicatorIterator;
    }
}