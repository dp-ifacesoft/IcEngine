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
        //по умолчанию
        'default'   =>  '/',
        '/'     =>  ['-', '\\', '|', '/'],
        '.'     =>  ['.', '..', '...', '....', '.....'],
        '.2'     =>  ['.', '..', '...', '....', '.....', '....', '...', '..'],
        ':)'    =>  [':)', ":|", ":(", ":|"],
        ':0'    =>  [':0', ':I', ':/']
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
     * вывести с индикатором
     * 
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
            echo str_repeat(' ', $nextLength)   . "\r" . chr(8) . $text . $next;
        } else {
            echo str_repeat(' ', $nextLength) . "\r" . chr(8) .$next;
        }
    }
    
    /**
     * вывести
     * 
     * @param string $text текст для отображения
     */
    public function printLine($text ='')
    {
        echo $text . PHP_EOL;
    }
    
    /**
     * вывести
     * 
     * @param string $text текст для отображения
     */
    public function linePrint($text ='')
    {
        echo PHP_EOL . $text;
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
    protected function getIndicatorType() 
    {
        if (!$this->_indicatorType) {
            $this->setIndicatorType($this->config['default']);
        }
        return $this->_indicatorType;
    }
    
    /**
     * установить текущий индикатор
     * @param integer $value
     */
    protected function setCurrentIndicator($value)
    {
        $this->_currentIndicatorIterator = $value;
    }
    
    /**
     * получить текущий итератор
     * @return integer
     */
    protected function getCurrentIndicator()
    {
        return $this->_currentIndicatorIterator;
    }
    
    /**
     * жирный текст
     * 
     * @param type $text
     */
    public function bold($text)
    {
        return "\033[1m" . $text . "\033[0m";
    }
    
    /**
     * Заполнить строку символом и перейти на новую
     * 
     * @param string $char символ
     */
    public function fillLine($char)
    {
        echo str_repeat($char, 80) . PHP_EOL;
    }
    
    /**
     * Перейти на новую и заполнить строку символом
     * 
     * @param string $char символ 
     */
    public function lineFill($char)
    {
        echo PHP_EOL . str_repeat($char, 80);
    }
    
    /**
     * Спросить пользователя
     * 
     * @param string $question вопрос
     * @param string $answer ответ
     * @param string $acceptCallback колбек положительный ответ
     * @param string $declineCallback колбек отрицательный ответ
     */
    public function confirm($question, $answer, $acceptCallback, $declineCallback = false)
    {
        $this->printLine($question);
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if (strtolower(trim($line)) == strtolower($answer)) {
            call_user_func($acceptCallback);
            return;
        } elseif($declineCallback) {
            call_user_func($declineCallback);
            return;
        }
    }
    
}
