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
     */
    public function update($data, $context)
    {
        $this->task->setTemplate(null);
        if (!$data) {
            return;
        }
        foreach ($data as $className => $annotationData) {
            $context->helperModelSync->resync($className);
            echo PHP_EOL . 'Resync model: ' . $className;
        }
        echo PHP_EOL;
    }
}