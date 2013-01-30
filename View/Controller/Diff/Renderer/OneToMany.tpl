<ul>
	{assign var="f" value=$field}
	{assign var="r" value=$renderer}
	{foreach from=$field->value item=v}
		<li>{$v->id}
			{if $r->fieldValueIsDeleted($f,$v)}
				<p>Была удалена</p>
			{else}
				{if $v->childRenderer->getModelEdits()->count()>0}
					{$v->childRenderer->render()}
				{else}
					<p>Нет изменений</p>
				{/if}
			{/if}
	{/foreach}
	{foreach from=$f->edits item=edit}
		{foreach from=$edit->value item=vv}
			{if !in_array($vv,$f->value->column('id'))}
				<li>{$vv}
					<p>Была добавлена</p>
				</li>
			{/if}
		{/foreach}
	{/foreach}
</ul>