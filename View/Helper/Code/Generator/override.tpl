<?php

return array(
    {foreach name="overrides" from=$overrides item="path" key="override"}
    '{$override}' => '{$path}',{if !$smarty.foreach.overrides.last}
    ,{/if}
    {/foreach}
);