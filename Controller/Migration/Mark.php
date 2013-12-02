<?php

/**
 * Контроллер для пометки миграций модели
 * 
 * @author morph
 */
class Controller_Migration_Mark extends Controller_Abstract
{
    /**
     * Проверить помечена ли миграция
     * 
     * @Context("helperMigrationMark")
     * @Template(null)
     * @ViewRender("Echo")
     * @Validator("Request_Method"={"get"})
     * @Route("/migration/mark/get/")
     */
    public function get($locationName, $migrationName, $context)
    {
        $isMarked = $context->helperMigrationMark->isMarked(
            $migrationName, $locationName
        );
        $this->output->send(array(
            'isMarked'  => (int) $isMarked
        ));
    }
    
    /**
     * Поменить миграцию
     * 
     * @Context("helperMigrationMark")
     * @Template(null)
     * @Validator("Request_Method"={"get"})
     * @Route("/migration/mark/set/")
     */
    public function set($locationName, $migrationName, $context)
    {
        $context->helperMigrationMark->mark($migrationName, $locationName);
    }
}