<?php

return array(
{if $services}
{foreach from=$services item="service" name="services"}
    '{$service.name}'   => array(
        'class' => '{$service.class}',
        {if !empty($service.isAbstract)}
        'isAbstract'    => true,
        {/if}
        {if !empty($service.args)}
        'args'  => array(
            {foreach from=$service.args item="arg" name="args"}
            {if is_numeric($arg) || is_bool($arg)}{$arg}{else}'{$arg}'{/if}{if !$smarty.foreach.args.last},{/if}
            {/foreach}
        ),
        {/if}
        {if !empty($service.disableConstruct)}
        'disableConstruct'      => true,
        {/if}
        {if !empty($service.source)}
        'source'    => array(
            {if !empty($service.source.name)}
            'name'  => '{$service.source.name}',
            {/if}
            'method'    => '{$service.source.method}',
            {if !empty($service.source.isAbstract)}
            'isAbstract'    => true,
            {/if}
            {if !empty($service.source.isStatic)}
            'isStatic'      => true
            {/if}
        )
        {/if}
    ){if !$smarty.foreach.services.last},{/if}
{/foreach}
{/if}
);