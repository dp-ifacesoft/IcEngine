{foreach from=$types item=type}
	<p>{$type->id}. {$type->name}/{$type->title}: {$type->data('count')};</p>
{/foreach}