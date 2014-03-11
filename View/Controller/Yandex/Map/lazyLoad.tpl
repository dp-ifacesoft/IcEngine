<script type="text/javascript">
	{if $hide_errors}
		{literal}
			if (typeof ymapifault == undefined){function ymapifault (err) {if ((typeof console).toLowerCase () == "object" && console.log) console.log ("yandex fail: ", err);};}
		{/literal}
	{/if}
		
	Controller_Yandex_Map.lazyLoadScript = 'http://api-maps.yandex.ru/1.1/index.xml?{if $load_by_require}loadByRequire=1&{/if}{if $hide_errors}onerror=ymapifault&{/if}{if $wizard}wizard={$wizard}&{/if}key={$key}';
</script>