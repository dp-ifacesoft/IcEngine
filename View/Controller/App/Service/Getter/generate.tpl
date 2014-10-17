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
    public static function get{$serviceName}()
    {
        return IcEngine::serivceLocator()
            ->getService('{lcfirst($serviceName)}');
    }
{/foreach}
}