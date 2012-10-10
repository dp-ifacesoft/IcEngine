<div id="edits">
	<ul class="fields">
	{foreach from=$fields item=field}
		<li>
			<h3 class="field-title-{$field->name}">{$field->type->config()->title}</h3>
			{$field->renderer->render($field,$parent)}
		</li>
	{/foreach}
	</ul>
</div>
{dblbracer}
<script type="text/javascript">
    $(document).ready(function() {
        $("#edits .close_map").click(function() {
            $("#edits .map").hide();
            return false;
        });
        $("#edits .show-geopoints").click(function() {
            var cont = $(this).parents("li")[0];
            var source = $(".yandex-geopoint:first",cont).text().split(",");
            var change = $(".yandex-geopoint:eq(1)",cont).text().split(",");
            var edit =  $("input[value=set-own]",cont).parents("li").find("input[type=text]")[0];
			$(".map",cont).show().find(".inner").yandexMap({
				point:source.lengt==2 ? source.join(',') : '',
				center:change.length==2 ? new YMaps.GeoPoint(change[0],change[1]) : "",
				center_selector:edit,
				zoom:14
			});
            
            return false;
        });
    });
</script>    
{/dblbracer}