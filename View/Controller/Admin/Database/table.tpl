<p class="control"><a href="/cp/db/">&larr; База данных</a></p>

<h2>Записи</h2>

	<p><a href="/cp/row/{$table}/0/{if isset($limitator)}?limitator={$limitator}{/if}">Добавить</a></p>

{if $collection}

	{if $search_fields && count($search_fields)>0}
	<div id="search" class="box white radius10">
		<form method="get" action="/cp/table/{$table}/">
			<div style="height:250px; overflow:auto">
				<table cellpadding="5">
					{foreach from=$search_fields item="sf"}
						{assign var="sff" value=$sf->Field}
						<tr>
							<td valign="top" width="150" style="padding-bottom:15px">
								{if !empty($sf->Comment)}
									{$sf->Comment}
								{else}
									{$sf->Field}
								{/if}
							</td>
							<td valign="top" style="padding-bottom:15px">
								{if !empty($sf->Values)}
									<select name="search[{$sf->Field}]" style="width:250px">
										<option value="0">Не выбрано</option>
										{foreach from=$sf->Values item="sfi"}
											<option value="{$sfi->key()}"{if $search[$sff]==$sfi->key()} selected{/if}>{$sfi->title()}</option>
										{/foreach}
									</select>
								{else}
									<input value="{if !empty($search[$sff])}{$search[$sff]}{/if}" name="search[{$sf->Field}]" type="text" style="width:250px" />
								{/if}
							</td>
						</tr>
					{/foreach}
				</table>
			</div>
			<p><input type="submit" value="Искать" /></p>
		</form>
	</div>
	{/if}

	<div id="inner" class="box white radius10 up">
	{if !$fields}
	<ul class="element-list">
		{foreach from=$collection item="i" name="i"}
		<li
		class="
		{if $smarty.foreach.i.iteration mod 2 eq 0}
			odd
		{else}
			even
		{/if}"
		style="
		{if $styles}
		{foreach from=$styles item="column" key="c"}
		{foreach from=$column item="style" key="v"}
		{if $i->$c==$v}{$style};{/if}
		{/foreach}
		{/foreach}
		{/if}
		"><span style="display:inline-block;width:40px"><a href="/cp/row/{$table}/{$i->key()}/">{$i->key()}</a></span>

		<a href="/cp/row/{$table}/{$i->key()}/" style="
			{if $link_styles}
				{foreach from=$link_styles item="column" key="c"}
					{foreach from=$column item="style" key="v"}
						{if $row->$c==$v}{$style};{/if}
					{/foreach}
				{/foreach}
			{/if}
		">{$i->title()}</a></li>
		{/foreach}
	</ul>
	{else}
	<table cellpadding="5" cellspacing="5" border="1" width="100%">
		<thead>
			<tr>
				{foreach from=$fields item="field"}
				<th>{if $field->Comment}{$field->Comment}{else}{$field->Field}{/if}</th>
				{/foreach}
			</tr>
		</thead>
		<tbody>
			{foreach from=$collection item="row" name="i"}
			<tr
				class="
				{if $smarty.foreach.i.iteration mod 2 eq 0}
					odd
				{else}
					even
				{/if}">
				{foreach from=$fields item="field" name="field"}
					{assign var="field" value=$field->Field}
					<td style="
						{if $styles}
							{foreach from=$styles item="column" key="c"}
								{foreach from=$column item="style" key="v"}
									{if $row->$c==$v}{$style};{/if}
								{/foreach}
							{/foreach}
						{/if}
					">
					{if $smarty.foreach.field.first}
					{if !$sfields || ($sfields && !in_array($keyField,$sfields))}
					<span style="display:inline-block;width:40px">
						<a href="/cp/row/{$table}/{$row->key()}/">{$row->key()}</a></span>
					{/if}
					{/if}
					{if $field == $title || ($links && in_array($field,$links))}
					<a href="/cp/row/{$table}/{$row->key()}/{if isset($limitator)}?limitator={$limitator}{/if}" style="
						{if $link_styles}
							{foreach from=$link_styles item="column" key="c"}
								{foreach from=$column item="style" key="v"}
									{if $row->$c==$v}{$style};{/if}
								{/foreach}
							{/foreach}
						{/if}
					">
					{if $field==$title}{$row->title()}{else}{$row->$field}{/if}</a>
					{else}{$row->$field}{/if}
					{if $limitators && in_array($field,$limitators)}
					{assign var="old" value=$row->data('old')}
					 (<a href="?limitator={$field}/{if isset($old[$field])}{$old[$field]}{else}{$row->$field}{/if}">огр.</a>)
					{/if}
					</td>
				{/foreach}
			</tr>
			{/foreach}
		</tbody>
	</table>
	{if isset($limitator)}
	<p><a href="/cp/table/{$table}/">Все записи</a></p>
	{/if}
	{/if}
	</div>
{/if}

{if isset($paginator_html)}
	<div class="box white radius10 up">
		{$paginator_html}
	</div>
{/if}

</div>