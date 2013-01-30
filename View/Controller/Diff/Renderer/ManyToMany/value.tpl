<ul>
	{foreach from=$value item=v}
		<li>{$renderer->getListItemById($v)}</li>
	{/foreach}
</ul>
