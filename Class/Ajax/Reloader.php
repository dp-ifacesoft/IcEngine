<?php

/**
 * Описание Reloader
 *
 * @author Apostle
 * @Service("ajaxReloader")
 */
class Ajax_Reloader 
{
    
    protected $html = [];
    
    /**
     * Перезагрузить данные
     * @param mixed $map - карта селектор-контроллеры
     */
    public function reload($map)
    {
        $serviceLocator = IcEngine::getServiceLocator();
        $debug = $serviceLocator->getService('debug');
        $controllerManager = $serviceLocator->getService('controllerManager');
        $html = [];
//        $map = (json_decode($map));
//        var_dump($map);
        foreach($map as $selector=>$controllerAction) {
            try {
                if(isset($controllerAction[1])) {
                    $html[$selector] = $controllerManager->html(
                        $controllerAction[0], [
                            'params' =>  $controllerAction[1]
                        ]
                    );
                } else {
                    $html[$selector] = $controllerManager->html(
                        $controllerAction[0]
                    );
                }
            } catch (Exception $ex) {
                $debug->log($ex->getMessage(), 'log');
            }

        }
        $this->html = $html;
//              $html = [];
//        foreach($map as $selector=>$controllerAction) {
//            $debug->log($controllerAction);
//            $args = [];
//            $args[] = $controllerAction[0];
//            if (isset($controllerAction[1])) {
//                $args['params'] = $controllerAction[1];
//            }
//            if (empty($args)) {
//                continue;
//            }
//            $html[$selector] = $controllerManager->html($args);
//        }
//        $this->html = $html;
        return $html;
    }
    
}
