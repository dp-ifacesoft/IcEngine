<p class="control"><a href="/cp/db/">База данных</a> / <a href="/cp/table/{$table}/">{$table}</a></p>

<h2>Запись</h2>

<div id="tabs">
	<ul>
		{foreach from=$tabs item="tab"}
		<li><a href="#tab-{$tab.action}">{$tab.name}</a></li>
		{/foreach}
	</ul>

	{foreach from=$tabs item="tab"}
		{assign var="tab" value=$tab.action}
		{assign var="cdir" value=$smarty.current_dir}
		{include file="$cdir/includes/$tab.tpl"}
	{/foreach}

</div>

{literal}
<script type="text/javascript">
$(function ()
{
	$('#tabs').tabs ();
});
</script>
{/literal}