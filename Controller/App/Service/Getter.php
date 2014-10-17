<?php

/**
 * Описание Getters
 *
 * @author Apostle
 */
class Controller_App_Service_Getter extends Controller_Abstract
{
    /**
     * Создать геттеры для сервисов
     * 
     * @Context("configManager")
     */
    public function generate($context)
    {
        $config = $context->configManager->get('Service_Source');
        $classes = [];
        foreach($config as $serviceName => $classObject) {
            $classes[ucfirst($serviceName)] = $classObject->class;
            
        }
        $this->output->send([
            'classes'   =>  $classes
        ]);
    }
    
   /**
    * обновить геттеры
    * @Template("null")
    */
    public function update()
    {
        $filename = IcEngine::root() . 'Ice/Class/App.php';
        $html = $this
            ->getService('controllerManager')
            ->html('App_Service_Getter/generate');
        file_put_contents($filename, $html);
    }
    
}
