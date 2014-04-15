var layers =
{
	data : [],

	init: function (layersDivId, mapDivId, longitude, latitude, zoom)
	{
		// ya map:
		this.map = new YMaps.Map (YMaps.jQuery (mapDivId) [0]);

		this.map.setCenter (
			new YMaps.GeoPoint (longitude, latitude),
			zoom,
			YMaps.MapType.MAP
		);

		this.map.addControl (new YMaps.Zoom ());
		this.map.addControl (new YMaps.ToolBar ());
		this.map.addControl (new YMaps.ScaleLine ());

		this.map.enableScrollZoom ({smooth:true});

		this.map.enableHotKeys ();

		//search
		(new YMaps.SearchControl (
		{
			useMapBounds: true,
			noCentering: false,
			noPlacemark: true,
			resultsPerPage: 5,
			width: 400
		})).onAddToMap (
			this.map,
			new YMaps.ControlPosition (
				YMaps.ControlPosition.TOP_RIGHT,
				new YMaps.Point (0, 0)
			)
		);

		// layers:
		this.layersDivId = layersDivId;

		this.showLayerList ();
	},

	// show list of registred layers
	showLayerList: function ()
	{
		var elm = YMaps.jQuery (this.layersDivId);

		elm.empty ();

		var form = YMaps.jQuery ('<form action="javascript:return false;"' +
			'onsubmit="return false;"/>'
		);

		form.appendTo (elm);

		for (var i = 0; i < this.data.length; i++)
		{
			var layerId = "layer" + i;
			var layerName = this.data [i].name;

			var box = YMaps.jQuery ('<div class="layer"/>');

			var checkbox = YMaps.jQuery ('<input type="checkbox"' +
				'id="' + layerId + '" name="' + layerId +
				'" value="' + layerName + '"/>');

			var label = YMaps.jQuery ('<label for="' + layerId + '">' +
				layerName + '</label>');

			checkbox.appendTo (box);

			label.appendTo (box);

			box.appendTo(form);

			checkbox.change (function()
			{
				if (this.checked)
				{
					layers.show (this.value);
				}
				else
				{
					layers.hide (this.value);
				}
			});

			this.hide (layerName);
		}
	},

	// creates overlay from object description
	createMapOverlay: function (objectDesc)
	{
		//type, point, style, description
		var points = objectDesc.points;

		if (points.length > 0)
		{
			for (var i = 0; i < points.length; i++)
			{
				points [i] = new YMaps.GeoPoint (
					points [i].lng,
					points [i].lat
				);
			}
		}
		else
		{
			points = new YMaps.GeoPoint (
				points.lng,
				points.lat
			);
		}

		if (points.length == 0)
		{
			return false;
		}

		var allowObjects = ['Placemark', 'Polyline', 'Polygon'],
			index = YMaps.jQuery.inArray (objectDesc.type, allowObjects),
			constructor = allowObjects [(index == -1) ? 0 : index];

		var description = objectDesc.description || "";

		var object = new YMaps [constructor]
		(
			points,
			{
				style: objectDesc.style,
				hasBalloon : !!description,
				hasHint: !!objectDesc.name
			}
		);

		object.description = description;

		object.name = objectDesc.name;

		return object;
	},

	// constructs new layer from description and adds it
	add: function (layerDesc)
	{
		var layer = {
			name: layerDesc.name,
			center: layerDesc.center, content: []
		};

		var i;

		// import styles
		for (i = 0; i < layerDesc.styles.length; i++)
		{
			YMaps.Styles.add (
				layerDesc.styles [i].name,
				layerDesc.styles [i].style
			);
		}

		// import objects
		for (i = 0; i < layerDesc.objects.length; i++)
		{
			var o = this.createMapOverlay (layerDesc.objects [i]);

			if (o)
			{
				layer.content [layer.content.length] = o;
			}
		}

		this.data[this.data.length] = layer;

	},

	// gets layer with name
	get: function (name)
	{
		for (var i = 0; i < this.data.length; i++)
		{
			if (this.data [i].name == name)
			{
				return this.data [i];
			}
		}
		return null;
	},

	// show layer with name
	show: function(name)
	{
		var layer = this.get (name);

		if (layer.show)
		{
			return;
		}

		for (var i = 0; i < layer.content.length; i++)
		{
			this.map.addOverlay(layer.content[i]);
		}

		layer.show = true;

		var point = new YMaps.GeoPoint (
			layer.center.lng,
			layer.center.lat
		);

		this.map.setZoom
		(
			layer.center.zoom,
			{
				smooth:true,
				position:point,
				centering:true
			}
		);
	},

	// hide layer with name
	hide: function (name)
	{
		var layer = this.get (name);

		if (!layer.show)
		{
			return;
		}

		for (var i = 0; i < layer.content.length; i++)
		{
			this.map.removeOverlay (layer.content [i]);
		}

		layer.show = false;
	}
};

function encodePoints (points)
{
	var array = [],                     // Временный массив для точек
		prev = new YMaps.Point (0, 0),    // Предыдущая точка
		coef = 1000000;                 // Коэффициент

	// Обработка точек
	for (var i = 0, geoVector, currentPoint; i < points.length; i++)
	{
		currentPoint = points[i].copy ();

		// Нахождение смещение относительно предыдущей точки
		geoVector = currentPoint.diff (prev).neg ();

		// Умножение каждой координаты точки на коэффициент и кодирование
		array = array.concat (
			Base64.encode4bytes (geoVector.getX () * coef),
			Base64.encode4bytes (geoVector.getY() * coef)
		);

		prev = currentPoint;
	}

	// Весь массив кодируется в Base64
	return Base64.encode (array);
};

$(document).ready (function ()
{
    if (typeof $('#saveGeoObject').live !== 'undefined')
    {
	$('#saveGeoObject').live ('click', function ()
	{
		var geo_layers = editor.getObjectDescriptions ();

		Controller.call (
			'Admin_Database_Map/save',
			{
				table: table,
				row_id: row_id,
				data: geo_layers
			},
			function (result)
			{

			},
			false
		);
	});
    }
});