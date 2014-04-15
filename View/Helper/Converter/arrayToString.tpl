{assign var='pad' value=$padding}
{assign var='pad2' value=$padding2}
{assign var='offs' value=$offset}
{if $data}
{if is_array($data) || is_object($data)}
array(
{if $data}
    {foreach from=$data item="field" key="name" name="field"}
    {$pad}{if !is_numeric($name)}'{$name}' => {/if}{IcEngine::getServiceLocator()->getService('helperConverter')->arrayToString($field,$offs+1)}{if !$smarty.foreach.field.last},{/if}

    {/foreach}
{/if}
{$pad}){else}
'{$data}'{/if}
{elseif !$data && !is_null($data)}
{$data}
{else}
null
{/if}