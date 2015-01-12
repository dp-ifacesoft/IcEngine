<?php

/**
 * Css Optimizator
 *
 * @author markov
 * @Service("serviceStaticCssOptimizator")
 */
class Service_Static_Css_Optimizator extends Service_Abstract
{
    /**
     * Запуск оптимизацию css файла
     * 
     * @param string $path
     */
    public function run($path)
    {
        exec('cd IcEngine/Static/nodejs && node cssoRun "' . $path . '" > /dev/null &');
    }
}