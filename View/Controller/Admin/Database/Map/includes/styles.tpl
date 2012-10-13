{dblbracer}
{{foreach from=$source item="style" name="style"}}
	{{if $style.name && $style.data}}
	{
		name: '{{$style.name}}',
		style: eval ('(' + '{{$style.data}}' + ')')
	}
	{{/if}}
	{{if !$smarty.foreach.style.last}},{{/if}}
{{/foreach}}
{/dblbracer}