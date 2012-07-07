<script>
	Controller_Plugin.init(
		'{$plugin.params.call}',
		{},
		{$plugin.pluginId}
	);
</script>
<input type="button" id="plugin{$plugin.pluginId}" value="{$plugin.params.name}" />