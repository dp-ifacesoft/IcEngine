<div id="edits">
	<ul class="fields">
	{foreach from=$fields item=field}
		<li>
			<h3>{$field->type->config()->title}</h3>
			{$field->renderer->render($field)}
		</li>
	{/foreach}
	</ul>
</div>