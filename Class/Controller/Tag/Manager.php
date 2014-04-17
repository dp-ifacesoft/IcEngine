<?php

/**
 * Менеджер тэгов контроллера
 * 
 * @author morph
 * @Service("controllerTagManager")
 */
class Controller_Tag_Manager extends Controller_Abstract
{
    /**
     * Текущие тэги
     * 
     * @var array
     */
    protected static $tags = array();
    
    /**
     * Добавить действие контроллера к тэгу
     * 
     * @param string $tag
     * @param string $controllerAction
     */
    public function append($tag, $controllerAction)
    {
        if (!isset(self::$tags[$tag])) {
            self::$tags[$tag] = array();
        }
        self::$tags[$tag][] = $controllerAction;
    }
    
    /**
     * Получить список контроллеров и их действий, привязанных к тэгам
     * 
     * @param mixed $tags
     * @return array
     */
    public function getFor($tags)
    {
        $config = $this->config();
        $result = array();
        foreach ((array) $tags as $tag) {
            if (!$config[$tag]) {
                continue;
            }
            $result = array_merge($result, $config[$tag]->__toArray());
        }
        return array_unique($result);
    }
    
    /**
     * Получить тэги
     * 
     * @return array
     */
    public function getTags()
    {
        return self::$tags;
    }
}