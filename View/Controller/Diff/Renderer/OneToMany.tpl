<ul>
	{assign var="f" value=$field}
	{assign var="r" value=$renderer}
	{foreach from=$field->value item=v}
		{assign var="deleted_edit" value=$r->fieldValueIsDeleted($f,$v)}
		{if $deleted_edit || $v->childRenderer->getModelEdits()->count()>0}
		<li class="{if $deleted_edit}row-deleted{/if}">
			{if $deleted_edit}
				{$v->childRenderer->renderModel()}
				<span>Была удалена&nbsp;<sup>Правка <a href="/cp/row/ice_user/{$deleted_edit->edit->User__id}/">пользователя #{$deleted_edit->edit->User->login}</a></sup></span>
				<ul>
					<li>
						<input type = "radio" name = "{$f->name}[{$v->id}][delete-edits]" id = "{$f->name}-{$v->id}-skip" checked value="skip"/>
						<label for="{$f->name}-{$v->id}-skip">Отложить решение</label>
					</li>
					<li>
						<input type = "radio" name = "{$f->name}[{$v->id}][delete-edits]" id = "{$f->name}-{$v->id}-cancel-delete" value="cancel"/>
						<label for="{$f->name}-{$v->id}-cancel-delete">Отменить удаление</label>
					</li>
					<li>
						<input type = "radio" name = "{$f->name}[{$v->id}][delete-edits]" id = "{$f->name}-{$v->id}-accept-delete" value="accept"/>
						<label for="{$f->name}-{$v->id}-accept-delete">Подтвердить удаление</label>
					</li>	
				</ul>
			{elseif $v instanceof Model}
				{if $v->childRenderer->getModelEdits()->count()>0}
					<input type = "hidden" name = "{$f->name}[{$v->id}][edits]" value="edits"/>
					{$v->childRenderer->renderModel()}
					{$v->childRenderer->render( $v->childRenderer->parentName() )}
				{/if}
			{/if}
		</li>
		{/if}
	{/foreach}
	{foreach from=$f->edits item=edit}
		{foreach from=$edit->value item=vv}
			{if !in_array($vv->id,$f->value->column('id'))}
				<li class="row-added">
					{$vv->childRenderer->renderModel()}
					<span>Была добавлена&nbsp;<sup>Правка <a href="/cp/row/ice_user/{$edit->edit->User__id}/">пользователя #{$edit->edit->User->login}</a></sup></span>
					<ul>
						<li>
							<input type = "radio" name = "{$f->name}[{$vv->id}][new-edits]" id = "{$f->name}-{$vv->id}-skip" checked value="skip"/>
							<label for="{$f->name}-{$vv->id}-skip">Отложить решение</label>
						</li>
						<li>
							<input type = "radio" name = "{$f->name}[{$vv->id}][new-edits]" id = "{$f->name}-{$vv->id}-cancel-new" value="cancel"/>
							<label for="{$f->name}-{$vv->id}-cancel-new">Отменить добавление</label>
						</li>
						<li>
							<input type = "radio" name = "{$f->name}[{$vv->id}][new-edits]" id = "{$f->name}-{$vv->id}-accept-new" value="accept" />
							<label for="{$f->name}-{$vv->id}-accept-new">Подтвердить добавление</label>
						</li>	
					</ul>


				</li>
			{/if}
		{/foreach}
	{/foreach}
</ul>