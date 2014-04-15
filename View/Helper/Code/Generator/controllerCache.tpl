<?php

return array(
    {if $profiles}
    'profiles'  => array(
        {foreach from=$profiles item="profile" key="profileName" name="profiles"}
        '{$profileName}'    => array(
            {if !empty($profile.expiration)}
            'expiration'    => {$profile.expiration},
            {if !empty($profile.tags)}
            'tags'  => array(
                {foreach from=$profile.tags item="tag" name="tags"}
                    '{$tag}'{if !$smarty.foreach.tags.last},
                    {/if}
                {/foreach}
            ),
            {/if}
            {if !empty($profile.vars)}
            'inputArgs'  => array(
                {foreach from=$profile.vars item="var" name="vars"}
                    '{$var}'{if !$smarty.foreach.vars.last},
                    {/if}
                {/foreach}
            )
            {/if}
            {/if}
        ){if !$smarty.foreach.profiles.last},
        {/if}
        {/foreach}
    ),
    {/if}
    {if $actions}
    'actions'  => array(
        {foreach from=$actions item="action" key="actionName" name="actions"}
        '{$actionName}'    => array(
            {if !empty($action.expiration)}
            'expiration'    => {$action.expiration},
            {if !empty($action.tags)}
            'tags'  => array(
                {foreach from=$action.tags item="tag" name="tags"}
                    '{$tag}'{if !$smarty.foreach.tags.last},
                    {/if}
                {/foreach}
            ),
            {/if}
            {if !empty($action.vars)}
            'inputArgs'  => array(
                {foreach from=$action.vars item="var" name="vars"}
                    '{$var}'{if !$smarty.foreach.vars.last},
                    {/if}
                {/foreach}
            ),
            {/if}
            {if !empty($action.cacheKey)}
            'cacheKey'  => array('{$action.cacheKey[0]}','{$action.cacheKey[1]}')
            {/if}
            {/if}
        ){if !$smarty.foreach.actions.last},
        {/if}
        {/foreach}
    )
    {/if}
);