<?php

/**
 * Контроллер синхронизации синхронизирующихся моделей
 * 
 * @author morph
 */
class Controller_Model_Sync extends Controller_Abstract
{
    /**
     * Синхронизовать модель
     * 
     * @Template(null)
     * @Context("helperModelSync")
     * @Validator("User_Cli")
     */
    public function resync($name, $context)
    {
        $context->helperModelSync->resync($name);
    }
}