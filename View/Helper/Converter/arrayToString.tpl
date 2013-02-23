{assign var='pad' value=$padding}
{assign var='pad2' value=$padding2}
{assign var='offs' value=$offset}
{if $data}{if is_array($data) or is_object($data)}
array (
{if count($data)}
{foreach from=$data item="field" key="name" name="field"}
{$pad}{if !is_numeric($name)}'{$name}' => {/if}{Helper_Converter::arrayToString($field,$offs+1)}{if !$smarty.foreach.field.last},{/if}

{/foreach}
{/if}
{$pad}){else}
'{$data}'{/if}{/if}