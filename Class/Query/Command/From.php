<?php

/**
 * Часть запроса from
 * 
 * @author morph
 */
class Query_Command_From extends Query_Command_Abstract
{
    /**
     * @inheritdoc
     */
    protected $mergeStrategy = Query::MERGE;
    
    /**
     * @inheritdoc
     */
    protected $part = Query::FROM;
    
    /**
     * Помощник для операции join
     * 
     * @var Helper_Query_Command_Join
     */
    protected static $helper;
 
    /**
     * @inheritdoc
     */
    public function create($data)
    {
        $this->data = $this->helper()->join(
            !empty($data[1]) ? $data : $data[0], Query::FROM
        );
        return $this;
    }
    
    /**
     * Получить хелпер
     * 
     * @return Helper_Query_Command_Join
     */
    public function getHelper()
    {
        return self::$helper;
    }
    
    /**
     * Получить (инициализировать) хелпер
     */
    protected function helper()
    {
        if (is_null(self::$helper)) {
            $serviceLocator = IcEngine::serviceLocator();
            self::$helper = $serviceLocator->getService(
                'helperQueryCommandJoin'
            );
        }
        return self::$helper;
    }
    
    /**
     * Изменить хелпер
     * 
     * @param Helper_Query_Command_Join $helper
     */
    public function setHelper($helper)
    {
        self::$helper = $helper;
    }
}