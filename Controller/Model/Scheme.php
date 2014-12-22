<?php

/**
 * Управление схемой модели (Model_Mapper)
 * 
 * @author morph
 */
class Controller_Model_Scheme extends Controller_Abstract
{
    /**
     * Создание новой схемы моделей
     * 
     * @Validator("User_Cli")
     * @Template(null)
     * @Context("helperModelScheme")
     */
    public function create($name, $context)
    {
        $dto = $context->helperModelScheme->createDefaultDto();
        $context->helperModelScheme->create($name, $dto);
    }
    
    /**
     * Пересинхронизировать схему с аннотаций
     * 
     * @Template(null)
     * @Validator("User_Cli")
     * @Context("helperModelMigrateSync")
     */
    public function resync($name, $context)
    {
        $context->helperModelMigrateSync->resync($name);
    }
}