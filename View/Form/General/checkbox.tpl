{foreach from=$element->selectable item="item"}
    <input name="{$element->name}{if count($element->selectable) > 1}[]{/if}" value="{$item.value}" {if in_array($item.value, $element->value)}checked="checked"{/if} type="checkbox" id="{$element->name}_{$item.value}">
    <label for="{$element->name}_{$item.value}">{$item.title}</label>
{/foreach}