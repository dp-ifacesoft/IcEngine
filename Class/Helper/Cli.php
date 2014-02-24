<?php

/**
 * Помощник для консоли 
 *
 * @author Apostle
 * @Service("Helper_Cli")
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
}