<?php

/**
 * Создание пропущенных моделей (тех, для которых есть схемы, но нет самих
 * моделей)
 * 
 * @author morph
 */
class Controller_Model_Missing extends Controller_Abstract
{
    /**
     * Создать пропущенные модели
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelMissing", "helperModelCreate")
     */
    public function create($context)
    {
        $missing = $context->helperModelMissing->getMissings();
        foreach ($missing as $modelName) {
            $dto = $this->getService('dto')->newInstance('Model_Create')
                ->setModelName($modelName);
            $context->helperModelCreate->create($dto);
        }
    }
}