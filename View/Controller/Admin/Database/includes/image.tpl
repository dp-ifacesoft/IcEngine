<div id="tab-image">
	{if $row}
	{*<script src="http://www.google.com/jsapi" type="text/javascript"></script>

	{literal}
	<script language="javascript" type="text/javascript">

		google.load('search', '1');

		function OnLoad() {

		  var searchControl = new google.search.SearchControl();

		  var localSearch = new google.search.LocalSearch();

		  searchControl.addSearcher(new google.search.ImageSearch());

		  searchControl.draw(document.getElementById("searchcontrol"));

		  searchControl.execute("{/literal}{if $row && isset($row->name)}{$row->name}{/if}{literal}");
		}

		google.setOnLoadCallback(OnLoad);

	</script>
	{/literal*}

	<div id="img-edit-dialog" title="Настройка изображения"></div>

	{*<div id="searchcontrol" style = "width: 300px; float: right;">Loading</div>*}
	<div id = "table-images"></div>
	<div style = "clear: both;"></div>

	{literal}
	<script type="text/javascript">
	$(function ()
	{
		Controller_Admin_Image.loadInterface(
			'{/literal}{$table}{literal}',
			'{/literal}{if $row}{$row->key()}{else}0{/if}{literal}'
		);
	});
	</script>
	{/literal}

	{else}
	<p>Перед добавлением изображений сохраните добавляемую запись.</p>
	{/if}
</div>

