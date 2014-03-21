{literal}
<script type="text/javascript">
$(function ()
{
	$('#save-text').click (function ()
	{
		Controller.call (
			'Component_Image/saveText',
			{
				id: $('#row-id').val (),
				text: $('#image-text').val ()
			},
			function (result)
			{

			},
			false
		);
	});
});
</script>
{/literal}

<input type="hidden" id="row-id" value="{$row->key()}" />
<p><img src="{$url}" /></p>
<p>Подпись: <input type="text" id="image-text" value="{$text}" /></p>
<p><input id="save-text" type="button" value="Применить" /></p>
