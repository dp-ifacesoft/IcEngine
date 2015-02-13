<?php

/**
 * Контроллер для аннотаций типа "AutoResync"
 * 
 * @author morph
 */
class Controller_Annotation_AutoResync extends Controller_Abstract
{
    /**
     * Обновить аннотации
     * 
     * @Context("helperModelSync")
     * @Template(null)
     * @Validator("Not_Null"={"data"})
     */
    public function update($data, $context)
    {
        foreach (array_keys($data) as $className) {
            $context->helperModelSync->resync($className);
        }
    }
}