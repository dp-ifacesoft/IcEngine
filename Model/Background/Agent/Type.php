<?php

class Background_Agent_Type extends Model
{
    
    /**
     * Запуск через командную строку
     * @var integer
     */
    const CMD = 1;
    
    /**
     * Запуск через http запрос
     * @var integer
     */
    const HTTP = 2;
    
}