<ul>
	<li>
		<input type = "radio" name = "{$field->name}-edits" id = "{$field->name}-skip" checked />
		<label for="{$field->name}-skip">Отложить решение</label>
	</li>
	<li>
		<input type = "radio" name = "{$field->name}-edits" id = "{$field->name}-leave-as-now" />
		<label for="{$field->name}-leave-as-now">Оставить как есть:</label>
		<span class="grey">{include file=$renderer->valueTemplate() value=$field->value}</span>
	</li>
	{foreach from=$field->edits item=edit key=edit_id}
	<li>
		<input type = "radio" name = "{$field->name}-edits" id = "{$field->name}-edit-{$edit_id}" />
		<label for="{$field->name}-edit-{$edit_id}">{include file=$renderer->valueTemplate() value=$edit->value}</label>
	</li>
	{/foreach}
	<li>
		<input type = "radio" name = "{$field->name}-edits" id = "{$field->name}-set" />
		<label for="{$field->name}-set">
			Указать свой вариант:
			{include file=$renderer->editTemplate() value=$field->value}
		</label>
	</li>	
</ul>
