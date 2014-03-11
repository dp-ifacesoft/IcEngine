<?php

/**
 * Помощник для консоли 
 *
 * @author Apostle
 * @Service("helperCli")
 */
class Helper_Cli extends Helper_Abstract
{
    private $indicator = '/';
    
    /**
     * крутилочка для визуального отбражения в консоли:)
     * @return string
     */
    public function next(){
        switch ($this->indicator) {
            case "/": $this->indicator = "-";
                break;
            case "-": $this->indicator = "\\";
                break;
            case "\\": $this->indicator = "|";
                break;    
            case "|": $this->indicator = "/";
                break;
            default: $this->indicator = "/";
                break;
        }
        return $this->indicator;
    }
    
    
    /**
     * очищает консоль
     */
    public function cls()
    {
        array_map(create_function('$a', 'print chr($a);'), array(27, 91, 72, 27, 91, 50, 74));
    }  
}