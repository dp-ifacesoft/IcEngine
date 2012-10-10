<select name="{$parent}{$field->name}-new-value">
	<option value=""></option>
	{foreach from=$renderer->getList() item=list_item}
		<option value="{$list_item->id}"{if $list_item->id==$value[0]} selected{/if}>{$list_item->name}</option>
	{/foreach}
</select>