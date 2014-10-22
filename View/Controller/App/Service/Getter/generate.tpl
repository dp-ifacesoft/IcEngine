<?php

/**
 * Автоматическое создание статических getter'ов для сервисов
 *
 * @autor Apostle
 */
class App extends App_Abstract
{
{foreach from=$classes key='serviceName' item="className"}

   /**
    * @return {$className}
    */
    public static function {$serviceName}()
    {
        return IcEngine::serviceLocator()
            ->getService('{$serviceName}');
    }
{/foreach}
}