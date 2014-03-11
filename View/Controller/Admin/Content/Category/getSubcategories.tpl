{if $items}
	<ul>
		{foreach from=$items item="c"}
			<li class="child level-{$level}{if $c.is_category} category{/if}">
				{if $c.is_category}
					<span onclick="Controller_Admin_Content_Category.toggleSubcategories(this,{$c.id},{$level})" id="category-{$c.id}">{$c.title}</span>
					(<a href="{$c.url}">Перейти</a>)
				{else}
					<span>{$c.title} (<a href="/content/create?referer=/cp/content/category&url=/cp/content/category&content_id={$c.id}">Редактировать</a>/<a href="{$c.url}">Перейти</a>)</span>
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}