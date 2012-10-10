{Controller call="Yandex_Map/includeScript"}	
{literal}
		<script type="text/javascript">
			function mapInit ()
			{
				var block = document.getElementById ("admin_map");
				block.innerHTML = '';
				block.style.height = 500 + 'px';
				
				if (typeof YMaps.Map != "function")
				{
					YMaps.load ();
					while (typeof YMaps.Map != "function")
					{
						console.log (typeof YMaps.Map);
					}
				}
				
				var map = new YMaps.Map (block);

				var x =$("#column-gp_longitude").val();
				var y =$("#column-gp_latitude").val();
				
				var name = $("#column-title").val();
				if (x != '' || y != '') {
				
					map.setCenter (new YMaps.GeoPoint (x, y), 6, YMaps.MapType.HYBRID);
					//map.openBalloon (new YMaps.GeoPoint (x, y), '', {hasCloseButton: true, mapAutoPan: 0});		
					var placemark = new YMaps.Placemark(map.getCenter(), {draggable: true});
					map.addOverlay(placemark);
				}
				
				else if (name!= '') {
					var geocoder = new YMaps.Geocoder(name);
			
					YMaps.Events.observe (geocoder, geocoder.Events.Load, function () {
						if (this.length())
						{
							map.setCenter (this.get(0).getGeoPoint(), 6, YMaps.MapType.HYBRID);
							//map.openBalloon (this.get(0).getGeoPoint(), name, {hasCloseButton: true, mapAutoPan: 0});
							$("#column-gp_longitude").val(this.get(0).getGeoPoint().getX());
							$("#column-gp_latitude").val(this.get(0).getGeoPoint().getY());
							var placemark = new YMaps.Placemark(map.getCenter(), {draggable: true});
							map.addOverlay(placemark);
						}
						else
						{
							$('#YMapsID').remove ();
							console.log ("Яндекс не нашел: "+name);
						}
					});

					YMaps.Events.observe(geocoder, geocoder.Events.Fault, function (error) {
						$('#YMapsID').remove ();
						console.log ("Произошла ошибка: " + error.message);
					});
				}
				// добавляем зум
				map.addControl (new YMaps.Zoom ({ }));

				// переключатель типа карты (спутник, гибрид)
				map.addControl (new YMaps.TypeControl ());
				
				var myEventListener = YMaps.Events.observe(map, map.Events.ContextMenu, function (map, mEvent) {
					placemark.setGeoPoint(mEvent.getGeoPoint());
					$("#column-gp_longitude").val(placemark.getGeoPoint().getX());
					$("#column-gp_latitude").val(placemark.getGeoPoint().getY());
				}, this);
				
				YMaps.Events.observe(placemark, placemark.Events.DragEnd, function (obj) {
					$("#column-gp_longitude").val(placemark.getGeoPoint().getX());
					$("#column-gp_latitude").val(placemark.getGeoPoint().getY());
				obj.update();
				});
				
			}
			
			$(function() {
				
				if ($("#column-gp_longitude").length==0) {
					$('#tabs a').each(function() {
						if ($(this).attr('href') == '#tab-map') {
							$(this).hide();
						}
					});
				}
				
				$('#tabs a').each(function() {
					if ($(this).attr('href') == '#tab-map') {
						$(this).bind('click', function() {
							setTimeout(function () {
								YMaps.load (mapInit);
							} , 500);
							
						});
					}
				})
			});
			
		</script>
	{/literal}
<div id="tab-map">
	<div id="admin_map">
	</div>
</div>
