{assign var="is_new" value=true}
{if $row && $row->sfield($row->keyField())}
	{assign var="is_new" value=false}
{/if}

{*literal}
	<script type="text/javascript">
	$(function () {
		editor ('extended');
	}); 
	</script>
{/literal*}

<div id="tab-data">

	{if !$is_new}
		<p><a href="/cp/row/{$table}/{$row->key()}/delete/" onclick="if(!confirm('Вы действительно хотите удалить запись?'))return false" style="color:red;font-weight:bold">Удалить</a></p>
	{/if}
		
	{if $fields}
	<form method="post" action="/cp/row/{$table}/save/">
	<input type="hidden" name="row_id" value="{if $is_new}0{else}{$row->key()}{/if}" />
	<table width="100%">
		{foreach from=$fields item="i" name="i"}
		{assign var="field" value=$i->Field}
		<tr style="background-color:{if $smarty.foreach.i.iteration mod 2 eq 1}#eee{else}#fff{/if}">
		{assign var="column" value=$i->Field}
			<td style="padding:6px" valign="top" width="150"><b>{if $i->Comment}{$i->Comment}{else}{$i->Field}{/if}:</b> </td> 
			<td style="padding:6px" valign="top">
				{if isset($i->Values)}
				<select id="column-{$i->Field}" name="column[{$i->Field}]"
					{if isset($events[$field])}
					{foreach from=$events[$field] item="method" key="event"}
					 on{$event}="{$method}"	
					{/foreach}
					{/if}
				>
					<option value="0">Не указано</option>
					{assign var="selected" value=0}
					{foreach from=$i->Values item="j"}
						<option value="{$j->key()}"{if isset($row->$column) && $row->$column==$j->key()} selected="selected"
						{assign var="selected" value=1}{/if}>
						{assign var="temp" value=$j->title()}
						{$temp|truncate:"100"}
						</option>
					{/foreach} 
					{if $row && isset($row->$column) && $row->$column && !$selected}
					<option value="{$row->$column}">Указано значение {$row->$column}</option>	
					{/if}
				</select>
				{elseif $i->Type=='tinyint(1)'}
				<input name="column[{$i->Field}]" type="hidden" value="0" />
				<input id="column-{$i->Field}" type="checkbox" value="1" name="column[{$i->Field}]"{if isset ($row->$column) && $row->$column} checked="checked"{/if}
					{if isset($events[$field])}
					{foreach from=$events[$field] item="method" key="event"}
					 on{$event}="{$method}"	
					{/foreach}
					{/if}
				>
				{elseif strpos($i->Type,'text')!==false}
				<p><span class="pseudo-link" onclick="toggleEditor('textarea-{$i->Field}')">WYSIWYG</span></p>
				<div style="width: 90%;">
				<textarea rows="8" cols="50" style="width: 250px;" id="textarea-{$i->Field}" name="column[{$i->Field}]"
					{if isset($events[$field])}
					{foreach from=$events[$field] item="method" key="event"}
					 on{$event}="{$method}"	
					{/foreach}
					{/if}
				>{if isset($row->$column)}{$row->$column|htmlspecialchars}{/if}</textarea>
				</div>
				{else}
				{if $i->Field==$keyField && $row->sfield($column)}
					<b>{$row->sfield($column)}</b>
				{else}
				<input id="column-{$i->Field}"{if strpos($i->Type,'time')!==false || strpos($i->Type,'date')!==false} class="icadmin_datepicker"{/if} size="44" type="text" name="column[{$i->Field}]" value="{if isset($row->$column)}{$row->$column|htmlspecialchars}{/if}"
					{if isset($events[$field])}
					{foreach from=$events[$field] item="method" key="event"}
					 on{$event}="javascript:{$method};"	
					{/foreach}
					{/if}	   
				>
				{/if}
				{/if}
			</td>
		</tr>
		{/foreach}
	</table>
	<p><input type="submit" value="Применить" />

	{if $plugins}
	{foreach from=$plugins item="action"}

	{Controller call=$action row=$row}
	{/foreach}
	{/if}

	</form>
	{/if}

</div>
	
{literal}
<script type="text/javascript">
$(function ()
{
	$('input[type=text].icadmin_datepicker').datepicker ({
		dateFormat: 'yy-mm-dd' 
	});
});
</script>
{/literal}