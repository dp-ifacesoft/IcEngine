<?php

/**
 * Автоматическое создание статических getter'ов для сервисов
 *
 * @autor Apostle
 */
class App
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