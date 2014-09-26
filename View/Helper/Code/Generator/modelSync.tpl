{if $data}(
{foreach from=$data item="model" name="models"}
    array(
    {foreach from=$model item="value" key="field" name="fields"}
        '{$field}' => '{$value|addslashes}'{if !$smarty.foreach.fields.last},
{/if}
    {/foreach}

    ){if !$smarty.foreach.models.last},

{/if}
{/foreach}
);{/if}