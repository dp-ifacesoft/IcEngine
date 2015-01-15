<?php

/**
 * Пул частей запроса
 * 
 * @author morph
 * @Service("queryCommandPool")
 */
class Query_Command_Pool
{
    /**
     * Брокер частей запроса
     * 
     * @var Query_Command_Broker
     */
    protected $commandBroker;
    
    /**
     * Свободные части запроса
     * 
     * @var array
     */
    protected $commands = array();
    
    /**
     * Добавить свободную комманду
     * 
     * @param Query_Command_Abstract $command
     */
    public function append($command)
    {
        $this->commands[] = $command;
    }
    
    /**
     * Получить (инициализировать) брокер частей запроса
     * 
     * @return Query_Command_Broker
     */
    public function commandBroker()
    {
        if (!$this->commandBroker) {
            $serviceLocator = IcEngine::serviceLocator();
            $this->commandBroker = $serviceLocator->getService(
                'queryCommandBroker'
            );
        }
        return $this->commandBroker;
    }
    
    /**
     * Получить свободную комманду
     * 
     * @param string $commandName
     */
    public function get($commandName)
    {
        $command = null;
        foreach ($this->commands as $i => $currentCommand) {
            if ($currentCommand->getName() == $commandName) {
                $command = $currentCommand;
                unset($this->commands[$i]);
                break;
            }
        }
        if (!$command) {
            $command = $this->commandBroker()->create($commandName);
        }
        return $command;
    }
    
    /**
     * Получить (без инициализации) брокер частей запроса
     * 
     * @return Query_Command_Broker
     */
    public function getCommandBroker()
    {
        return $this->commandBroker;
    }
    
    /**
     * Изменить брокер частей запроса
     * 
     * @param Query_Command_Broker $commandBroker
     */
    public function setCommandBroker($commandBroker)
    {
        $this->commandBroker = $commandBroker;
    }
}