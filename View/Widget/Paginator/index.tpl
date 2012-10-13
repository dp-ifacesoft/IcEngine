{if $paginator->pagesCount() > 1}
	<div id="page_list" class="pages">
			{foreach from=$paginator->pages item=page}
				{if isset($page.selected) && $page.selected}
					<span class="active">{$page.title}</span>
				{elseif !$page.href}
					{$page.title}
				{else}
					<a href="{$page.href}">{$page.title}</a>
				{/if}
			{/foreach}
	</div>
	<div class="clear"></div>
{/if}