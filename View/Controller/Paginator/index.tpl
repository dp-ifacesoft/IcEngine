<div id="pages">
	{foreach from=$paginator->pages item="page"}
		{if $page.selected}
			<span><span>Страница {$page.title}</span></span>
		{elseif isset($page.href) && $page.href}
			<a href="{$page.href}">{$page.title}</a>
		{else}
			{$page.title}
		{/if}
	{/foreach}
</div>