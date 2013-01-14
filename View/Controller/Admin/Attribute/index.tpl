{if !empty($model)}
<h3>
<a id="view" href="javascript:void(0);" onclick="Controller_Admin_Attribute.viewFormAdd();">Добавить атрибут</a>
</h3>

<div id="add_attr" style="display:none;">
	<form action="javascript:void(0);" onsubmit="Controller_Admin_Attribute.add(this);">
		<input type="hidden" name="table" value="{$model->modelName()}"></input>
		<input type="hidden" name="rowId" value="{$model->key()}"></input>
		Название атрибута: <input type="text" name="key"></input>
		<br />
		Значение атрибута: <textarea name="value"></textarea>
		<br />
		<a href='javascript:void(0);' onclick="$('#add_attr form').submit();">Добавить</a>
	</form>
</div>

<br />
<table width="100%" cellspacing="5" cellpadding="5" border="1">
	<thead>
		<tr>
			<th>Название атрибута</th>
			<th>Значение</th>
			<th>Редактировать</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$attribute_collection item='attribute'}
			<tr>
				<td><a href="/cp/row/ice_attribute/{$attribute->key()}/" target="about_blank">{$attribute->key}</a></td>
			        <td><a href="/cp/row/ice_attribute/{$attribute->key()}/" target="about_blank">{$attribute->value}</a></td>
				<td><a href="/cp/row/ice_attribute/{$attribute->key()}/" target="about_blank">редактировать</a></td>
			</tr>
		{/foreach}
	</tbody>
</table>
	
{/if}	
