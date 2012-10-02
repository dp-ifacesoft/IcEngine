{dblbracer}
<script type="text/javascript">
	var mapLoaded = false, table, row_id;

	// Показываем карту
	function ShowMap (cont_id, t, rid)
	{
		table = t;
		row_id = rid;

		if (!mapLoaded)
		{
			editor.init (
				'#' + cont_id,
				{{$long}},
				{{$lat}},
				11
			);

			mapLoaded = true;
		}
	}

	{{assign var="cdir" value=$smarty.current_dir}}

	var this_record_layer = {
		objects: [
			{{if $geo_points->count()}}
				{{include file="$cdir/includes/points.tpl"
					type="Placemark"
					source=$geo_points
				}}

				{{if $geo_polylines->count() || $geo_polygons->count()}},{{/if}}

			{{/if}}

			{{if $geo_polylines->count()}}
				{{include file="$cdir/includes/points.tpl"
					type="Polyline"
					source=$geo_polylines
				}}

				{{if $geo_polygons->count()}},{{/if}}

			{{/if}}

			{{if $geo_polygons->count()}}
				{{include file="$cdir/includes/points.tpl"
					type="Polygon"
					source=$geo_polygons
				}}
			{{/if}}
		],

		styles: [
			{{include file="$cdir/includes/styles.tpl"
				source=$styles
			}}
		]
	};

	var editor =
	{
		// predifined placemarks styles
		predefinedStyles : [
			{{foreach from=$style_collection item="style" name="style"}} {
				style:'{{$style->name}}',
				url:'{{if isset($style->data)}}{{$style->data}}{{/if}}'
			},
			{{if !$smarty.foreach.style.iteration}},{{/if}}
			{{/foreach}}
		],
		{{include file="$cdir/includes/js.tpl"}}
	}

	$(function ()
 	{
		var mapTimer = setInterval (
 			function ()
 			{
				if ($('li.ui-state-active a[href="#tab-geo"]').length)
				{
					$('#editor').prependTo ($('#tab-geo'));
					ShowMap ('editor', '{{$table}}', {{$row_id}});
					clearInterval (mapTimer);
				}
 			},
			300
 		);
 	});
</script>
{/dblbracer}

<div id="editor" style="height:600px"></div>

<div id="tab-geo">
	<p style="margin-top:10px">Редактирование объекта:</p>

	<div id="toolsBox" style="background-color:#f3f3f3; padding:10px; border:solid 1px #333">
        <form action="javascript:return false;" onsubmit="return false;">
            <div id="tools" style="padding:2pt;">
                <div class="layer" style="display:none; background-color:#f3f3f3;margin:0;/*padding:5px;*/">
                    <input class="save" type="hidden" value="Сохранить слой" style="width:185px;" />

                    <input type="text" name="layerName" class="layerName" size="30" style="width:185px;" />
                    <input type="text" name="layerContent" class="layerContent" />
                </div>
                <div class="baloon">
					<table border="0" cellpadding="5" cellspacing="0" width="100%">
						<tr>
							<td width="120" align="left" valign="top">
								Имя
							</td>
							<td align="left" valign="top">
								<input type="text" name="name" class="overlayName" style="width:100%" />
							</td>
						</tr>
						<tr>
							<td width="120" align="left" valign="top">
								Описание
							</td>
							<td align="left" valign="top">
								<textarea name="desc" class="overlayDescription mceNoEditor" style="width:100%; height:100px"></textarea>
							</td>

						</tr>
					</table>


					<p style="margin:10px 0"><span id = "saveGeoObject" class = "pseudo-link" style="border-bottom:dashed 1px; color:#aaa">Cохранить геоданные</span></p>

				</div>
                <table style="width:185px;" class="remover">
                    <tbody>
						<tr>
							<td colspan="2">Объект</td>
						</tr>

						<tr>
							<td style="text-align:left;"><input class="show" type="button" value="Показать" /></td>
							<td style="text-align:right;"><input class="remove" type="button" value="Удалить" /></td>
						</tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

</div>