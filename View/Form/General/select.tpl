<select name="{$element->name}" {foreach from=$element->attributes item='attr' key='key'}{$key}="{$attr}" {/foreach} />
    {foreach from=$element->selectable item="item"}
        <option value="{$item.value}" {if $item.value==$element->value}selected="selected"{/if}>{$item.title}</option>
    {/foreach}
</select>