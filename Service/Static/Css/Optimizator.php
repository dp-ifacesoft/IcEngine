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
     * @param string $pathFile
     */
    public function run($pathFile)
    {
        $path = IcEngine::root() . 'IcEngine/Static/nodejs';
        exec('cd ' . $path . ' && node cssoRun.js "' . $pathFile . '" > /dev/null &');
    }
}