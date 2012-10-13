<h2>Таблицы</h2>

{if $tables}
<ul class="element-list">
	{foreach from=$tables item="i"}
	<li><a href="/cp/table/{$i->Name}/">{if $i->Comment} <span class="highter">{$i->Comment}</span> <span class="lower">({$i->Name})</span>{else}{$i->Name}{/if}</a></li>
	{/foreach}
</ul>
{/if}
