<ul>
	<li>
		<input type = "radio" name = "{$parent}{$field->name}-edits" id = "{$field->name}-skip" checked value="skip"/>
		<label for="{$field->name}-skip">Отложить решение</label>
	</li>
	<li>
		<input type = "radio" name = "{$parent}{$field->name}-edits" id = "{$field->name}-leave-as-now"  value="leave-as-now"/>
		<label for="{$field->name}-leave-as-now">Оставить как есть:</label>
		<span class="grey">{include file=$renderer->valueTemplate() value=$field->value}</span>
	</li>
	{foreach from=$field->edits item=edit key=edit_id}
	<li>
		<input type = "radio" name = "{$parent}{$field->name}-edits" id = "{$field->name}-edit-{$edit_id}" value="change-{$edit_id}" />
		<label for="{$field->name}-edit-{$edit_id}">Правка <a href="/cp/row/ice_user/{$edit->edit->User__id}/">пользователя {$edit->edit->User->login}</a>:
			{include file=$renderer->valueTemplate() value=$edit->value}
			
		</label>
	</li>
	{/foreach}
	<li>
		<input type = "radio" name = "{$parent}{$field->name}-edits" id = "{$field->name}-set" value="set-own" />
			<label for="{$field->name}-set">Указать свой вариант:</label>
			{include file=$renderer->editTemplate() value=$field->value}
	</li>	
</ul>
{if $field->type instanceof Diff_Geopoint}
    <div style="position:relative;">
        <a href="#" class="show-geopoints">Посмотреть на карте</a>
        <div class="map" style="position:absolute;width:500px;height:350px;top:0;left:0;display:none;padding:25px 15px 15px;background-color:#1a1a1a;">
            <div class="close_map" style="position:absolute;right:15px;top:0;color:#fff;cursor:pointer;">X</div>
            <div class="inner" style="position:relative;width:100%;height:100%;"></div>
        </div>
    </div>
{/if}