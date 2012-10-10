<div id="tab-banner" style="padding:0;">
	<div id="banner-edit-dialog" title="Настройка баннера"></div>
	<div id="banner-ajax-buffer"></div>

	<div id = "table-banners"></div>

	{literal}
	<script type="text/javascript">
	$(function ()
	{
		Controller_Admin_Banner.loadInterface (
			'{/literal}{$table}{literal}',
			'{/literal}{if $row}{$row->key()}{else}0{/if}{literal}'
		);
	});
	</script>
	{/literal}

</div>
