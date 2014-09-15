<?php

return array(
{if $config}
{foreach from=$config item="migrations" key="locationName" name="locations"}
    '{$locationName}' => array(
        {foreach from=$migrations item="value" key="migrationName" name="migrations"}
        '{$migrationName}' => true{if !$smarty.foreach.migrations.last},
        {/if}
        {/foreach}
    ){if !$smarty.foreach.locations.last},
    {/if}
{/foreach}
{/if}
);