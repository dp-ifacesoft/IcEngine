<p>
	{foreach from=$paginator->pages item="page"}
		{if $page.selected}
			<b>{$page.title}</b>
		{elseif isset($page.href) && $page.href}
			<a href="{$page.href}">{$page.title}</a>
		{else}
			{$page.title}
		{/if}
	{/foreach}
</p>