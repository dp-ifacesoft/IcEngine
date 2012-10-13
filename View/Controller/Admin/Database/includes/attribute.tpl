<div id="tab-attribute">
	<div id="attrs"></div>
</div>
	
{literal}
<script type="text/javascript">
	$(function () {
		
		function callback (result)
		{
			$('#attrs').html (result.html);
		};		
		
		Controller.call (
			'Admin_Attribute/index',
			{ 
				table : '{/literal}{if !empty($row)}{$row->modelName()}{/if}{literal}',
				rowId : '{/literal}{if !empty($row)}{$row->key()}{/if}{literal}'
			},
			callback
		);
	});
</script>
{/literal}	