<?php

/**
 * Хелпер для работы с аннотациями
 * 
 * @author morph
 * @Service("helperAnnotation")
 */
class Helper_Annotation extends Helper_Abstract
{
    /**
     * Получить аннотации объекта
     * 
     * @param mixed $mixed
     * @return Annotation_Set
     */
    public function getAnnotation($mixed)
    {
        return $this->getManager()->getAnnotation($mixed);
    }
    
    /**
     * Получить менеджер аннотаций
     * 
     * @return Annotation_Manager_Abstract
     */
    public function getManager()
    {
        return IcEngine::serviceLocator()->getSource()->getAnnotationManager();
    }
}