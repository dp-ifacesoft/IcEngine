<?php

/**
 * копилка геттеров
 *
 */
class App
{
{foreach from=$classes key='serviceName' item="className"}

   /**
    * @return {$className}
    */
    public static function get{$serviceName}()
    {
        return $this->getService({lcfirst($serviceName)});
    }
{/foreach}
}