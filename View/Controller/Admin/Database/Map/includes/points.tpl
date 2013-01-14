{dblbracer}
{{foreach from=$source item="point" name="point"}}
	{
		{{if $point instanceof Component_Geo_Point}}
		points: {
			lng: {{$point->longitude}}, 
			lat: {{$point->latitude}}
		},
		{{else}}
		points: [
			{{foreach from=$point->points() item="p" name="pp"}}
				{
					lng: {{$p->longitude}}, 
					lat: {{$p->latitude}}
				}
			{{if !$smarty.foreach.pp.last}},{{/if}}
			{{/foreach}}
		],
		{{/if}}
		type: '{{$type}}',
		description: '',
		name: '',
		draggable: true,
		hasBaloon: false,
		hasHint: true,
		style: '{{$point->style()}}'
	}
	{{if !$smarty.foreach.point.last}},{{/if}}
{{/foreach}}
{/dblbracer}