<?php

return array(    
    {foreach name="fields" from=$data key="fieldName" item="roles"}
        {if $roles}
        '{$fieldName}' => array(
            {foreach from=$roles name="roles" item="accessTypes" key="roleName"}
            '{$roleName}' => array({foreach from=$accessTypes item="accessType" name="accessTypes"}'{$accessType}'{if !$smarty.foreach.accessTypes.last}, {/if}{/foreach}){if !$smarty.foreach.roles.last}, 
            {/if}
            {/foreach}
        ){if !$smarty.foreach.fields.last},
        {/if}
        {/if}
    {/foreach}
);