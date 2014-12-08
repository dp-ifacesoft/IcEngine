<?php
/**
 * Менеджер экстракторов параметров маршрута
 *
 * @author LiverEnemy
 * @Service("routeParamExtractorManager")
 */

class Route_Param_Extractor_Manager extends Manager_Simple
{
    /**
     * @inheritdoc
     *
     * @return Route_Param_Extractor
     */
    public function get($name, $default = null)
    {
        return parent::get($name, $default);
    }
} 