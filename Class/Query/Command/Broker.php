<?php

/**
 * Брокер частей запроса
 * 
 * @author morph
 * @Service("queryCommandBroker")
 */
class Query_Command_Broker
{
    /**
     * Создать комманду
     * 
     * @param string $commandName
     * @return Query_Command_Abstract
     */
    public function create($commandName)
    {
        $className = 'Query_Command_' . $commandName;
        $command = new $className;
        return $command;
    }
    
    /**
     * Получить имя класса части запроса по имени метода
     * 
     * @param string $name
     * @return string
     */
    public function getClassName($name)
    {
        $matches = array ();
		$reg_exp = '#([A-Z]*[a-z]+)#';
		preg_match_all($reg_exp, $name, $matches);
		if (empty ($matches[1][0])) {
			return $name;
		}
		return implode('_', array_map('ucfirst', $matches[1]));
    }
}