{literal}
	// list of maps overlays
	data : [],	
	
    otherData: [],
	
	// prefix for polyline/polygon styles
    layerName : 'ymikEditor#' + new Date ().getTime (), 

    init: function (mapDivId, longitude, latitude, zoom) 
	{

        // ya map:
		var mapEditor = this;

		YMaps.load (function ()
		{
			mapEditor.map = new YMaps.Map (YMaps.jQuery (mapDivId) [0]);

			mapEditor.map.setCenter (
				new YMaps.GeoPoint (longitude, latitude), 
				zoom, 
				YMaps.MapType.MAP
			);

			mapEditor.map.addControl (new YMaps.Zoom ());
			mapEditor.map.addControl (new YMaps.ToolBar ());
			mapEditor.map.addControl (new YMaps.TypeControl ());
			mapEditor.map.addControl (new YMaps.ScaleLine ());

			mapEditor.map.enableScrollZoom ({smooth:true});
			mapEditor.map.enableHotKeys ();

			//search
			(new YMaps.SearchControl (
			{
				useMapBounds:true,
				noCentering:false,
				noPlacemark:true,
				resultsPerPage:5,
				width:400
			})).onAddToMap (
				mapEditor.map, 
				new YMaps.ControlPosition (
					YMaps.ControlPosition.TOP_RIGHT, 
					new YMaps.Point (0, 0)
			));

			// create editortoolbar
			var toolbar = new YMaps.ToolBar ();

			mapEditor.addPlacemarkTool (toolbar);
			mapEditor.addPolylineTool (toolbar);
			mapEditor.addPolygonTool (toolbar);

			toolbar.onAddToMap (mapEditor.map);

			// controls
			mapEditor.addDescriptionEditorControl ();
			mapEditor.addPlacemarkSelectorControl ();
			mapEditor.addLineStyleControl ();
			mapEditor.addRemoverControl ();
			mapEditor.addLayerControl ();

			// default
			mapEditor.setPlacemarkStyle ('default#redPoint');

			mapEditor.setLineStyle ({
				strokeColor:'ff000080',
				strokeWidth:5
			});

			mapEditor.showPlacemarkControl();

            mapEditor.importLayer(this_record_layer);
		});
    },

    // enable editing mode for overlay
    // setup editor controls & so one
    startEditing: function (overlay) 
	{
        if (this.currentObject != overlay) 
		{
            this.stopEditing ();
            this.currentObject = overlay;
            this.removerControl.show ();
        }

        if (overlay instanceof YMaps.Polyline) 
		{
            overlay.startEditing();
            this.setLineStyle (YMaps.Styles.get (overlay.getStyle ()).lineStyle);
        } 
		else if (this.currentObject instanceof YMaps.Polygon) 
		{
            overlay.startEditing();
            this.setLineStyle (YMaps.Styles.get (overlay.getStyle()).polygonStyle);
        } 
		else if (overlay instanceof YMaps.Placemark) 
		{
            this.setPlacemarkStyle (overlay.getOptions ().style);

        } 
		else
		{
			return;
		}
		
        this.setDescription (overlay);

        this.overlayName.focus ();
        this.overlayName.select ();
    },

    // apply all changes & stop editing mode for current overlay
    stopEditing: function () 
	{
        if (this.currentObject) 
		{
            if (this.currentObject instanceof YMaps.Polyline) 
			{
                this.currentObject.stopEditing ();
                this.currentObject.setEditingOptions ({drawing:false});
            } 
			else if (this.currentObject instanceof YMaps.Polygon) 
			{
                this.currentObject.stopEditing ();
                this.currentObject.setEditingOptions ({drawing:false});
            } 
			else if (this.currentObject instanceof YMaps.Placemark) 
			{
                this.setPlacemarkStyle ();
            }

            this.setDescription ();
        }
    },

    // setup {name, description} for DescriptionControl & 
	// apply description to current overlay
    setDescription: function (description) 
	{
        if (description) 
		{
            this.overlayName.val (description.name);
            this.overlayDescription.val (description.description);
        }

        if (this.currentObject) 
		{
            this.currentObject.name = this.overlayName.val ();
            this.currentObject.description = this.overlayDescription.val ();
            this.currentObject.update ();
        }
    },

    // setup PlacemarkStyle for PlacemarkSelectorControl & 
	// apply style for current overlay
    setPlacemarkStyle: function (style) 
	{
        if (style) 
		{
            if (this.currentPlacemarkStyleElm) 
			{
                this.currentPlacemarkStyleElm.className = 'placemarkStyle';
            }

            var marks = YMaps.jQuery ('#tools .placemark .placemarkStyle');

            for (i = 0; i < marks.length; i++) 
			{
                if (marks[i].id == style) 
				{
                    this.currentPlacemarkStyleElm = marks [i];
                    break;
                }
            }

			if (this.currentPlacemarkStyleElm)
			{
				this.currentPlacemarkStyleElm.className = 
					'placemarkStyle selectedPlacemarkStyle';
			}
			
            this.placemarkStyle = style;
        }

        if (this.currentObject instanceof YMaps.Placemark) 
		{
            var options = this.currentObject.getOptions ();
            options.style = this.placemarkStyle;
            this.currentObject.setOptions (options);
            this.currentObject.update ();
        }
    },

    // setup Style for LineStyleControl & apply style for current overlay
    // style may be YMaps.LineStyle, YMaps.PolygonStyle or null;
    // in last case will be apply current style from LineStyleControl 
    setLineStyle: function (style) 
	{
        var alpha, color;

        if (style) 
		{
            alpha = style.fillColor 
				? style.fillColor.substr (6, 2) 
				: style.strokeColor.substr (6, 2);

            color = style.strokeColor.substr (0, 6);

            this.lineSample.css ('backgroundColor', '#' + color);
            this.lineSample.css ('height', style.strokeWidth);

            this.colorSelector.val (color);

            this.withSelector.val (style.strokeWidth);

            this.alphaSelector.val (alpha);

            this.lineStyle = style;

        } 
		else 
		{
            alpha = this.lineStyle.fillColor 
				? this.lineStyle.fillColor.substr (6, 2) 
				: this.lineStyle.strokeColor.substr (6, 2);

            color = this.lineStyle.strokeColor.substr (0, 6);
        }

        if (this.currentObject instanceof YMaps.Polyline) 
		{
            var s = YMaps.Styles.get (this.currentObject.getStyle ());

            if (!s.lineStyle) 
			{
                s.lineStyle = new YMaps.LineStyle ();
            }

            s.lineStyle.strokeColor = this.lineStyle.strokeColor;
            s.lineStyle.strokeWidth = this.lineStyle.strokeWidth;
            this.currentObject.update ();
        } 
		else if (this.currentObject instanceof YMaps.Polygon) 
		{
            var s = YMaps.Styles.get (this.currentObject.getStyle ());

            if (!s.polygonStyle) 
			{
                s.polygonStyle = new YMaps.PolygonStyle ();
            }

            s.polygonStyle.strokeColor = color + 'ff';
            s.polygonStyle.fillColor = color + alpha;
            s.polygonStyle.outline = true;
            s.polygonStyle.strokeWidth = this.lineStyle.strokeWidth;
            this.currentObject.update();
        }
    },

    // creates new named style for YMaps.Polyline or YMaps.Polygon
    createStyle: function() 
	{
        var key = this.layerName + '#' + this.data.length;
        YMaps.Styles.add (key, new YMaps.Style ());
        return key;
    },

    // returns StyleDescription for overlay saving
    getStyleDescription:function(overlayNumber) 
	{
        if (this.data [overlayNumber]) 
		{
            var returningStyle;

            var s = YMaps.Styles.get (this.data [overlayNumber].getStyle ());

            if (this.data[overlayNumber] instanceof YMaps.Polyline) 
			{
                returningStyle = {lineStyle : {
					strokeColor:s.lineStyle.strokeColor,
					strokeWidth:s.lineStyle.strokeWidth
				}};
            } 
			else if (this.data [overlayNumber] instanceof YMaps.Polygon) 
			{
                returningStyle = {polygonStyle : {
					strokeColor:s.polygonStyle.strokeColor,
					strokeWidth:s.polygonStyle.strokeWidth,
					fillColor:s.polygonStyle.fillColor,
					fill:true,
					outline:true
				}};
            }
        }

        return returningStyle;
    },

    // returns array of StyleDescription for exportLayer function
    getStyleDescriptions: function () 
	{
        var styles = [];

        for (var i = 0; i < this.data.length; i++)
		{
            if (this.data [i]) 
			{
                var s = YMaps.Styles.get (this.data [i].getStyle ());

                if (this.data[i] instanceof YMaps.Polyline) 
				{
                    styles[styles.length] = {
                        name:this.data[i].getStyle (),
                        style:{lineStyle : {
                            strokeColor:s.lineStyle.strokeColor,
                            strokeWidth:s.lineStyle.strokeWidth
                        }}
                    };
                } 
				else if (this.data [i] instanceof YMaps.Polygon) 
				{
                    styles [styles.length] = {
                        name:this.data [i].getStyle (),
                        style:{polygonStyle : {
                            strokeColor:s.polygonStyle.strokeColor,
                            strokeWidth:s.polygonStyle.strokeWidth,
                            fillColor:s.polygonStyle.fillColor,
                            fill:true,
                            outline:true
                        }}
                    };
                }
            }
		}
        return styles;
    },

    // returns arry of ObjectDescription for exportLayer function
    getObjectDescriptions: function () 
	{
        function convertPoint (point) 
		{
            return {
                lng:point.getX (),
                lat:point.getY ()
            };
        }

        function convertPoints (points) 
		{
            var r = [];

            for (var i = 0; i < points.length; i++)
			{
                r [r.length] = convertPoint (points[i]);
			}
			
            return r;
        }

        var objects = [];

        for (var i = 0; i < this.data.length; i++)
		{
            if (this.data[i] != null) 
			{
                var o = {
                    style:this.data [i].getStyle (),
                    name:this.data [i].name,
                    description:this.data [i].description
                };

                if (this.data[i] instanceof YMaps.Polyline) 
				{
                    o.type = 'Polyline';
                    o.points = convertPoints (this.data [i].getPoints ());
                    o.encodedPoints = encodePoints (this.data [i].getPoints ());
                    o.serializedStyle = this.getStyleDescription (i);

                } 
				else if (this.data[i] instanceof YMaps.Polygon) 
				{
                    o.type = 'Polygon';
                    o.points = convertPoints (this.data [i].getPoints ());
                    o.encodedPoints = encodePoints (this.data [i].getPoints ());
                    o.serializedStyle = this.getStyleDescription (i);
                } 
				else if (this.data[i] instanceof YMaps.Placemark) 
				{
                    o.type = 'Placemark';
                    o.points = convertPoint (this.data [i].getGeoPoint ());
                }

                objects[objects.length] = o;
            }
		}
        return objects;
    },

    // exports Editor's data into LayerDescription object
    exportLayer: function (layerName) 
	{
        return {
            name:layerName,
            center: {
                lng:this.map.getCenter ().getX (),
                lat:this.map.getCenter ().getY (),
                zoom:this.map.getZoom ()
            },
            styles:this.getStyleDescriptions (),
            objects:this.getObjectDescriptions ()
        };
    },

    // import layer in LayerDescription format into the Editor
    importLayer: function (layer) 
	{
        // creates overlay from ObjectDescription
        function createMapOverlay (objectDesc) 
		{
            var points = objectDesc.points;

			if (!points)
			{
				return false;
			}
			
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
                points = new YMaps.GeoPoint (points.lng, points.lat);
            }

			if (points.length == 0)
			{
				return false;
			}
			
            var allowObjects = ['Placemark', 'Polyline', 'Polygon'],
				index = YMaps.jQuery.inArray (objectDesc.type, allowObjects),
				constructor = allowObjects [(index == -1) ? 0 : index];

            var description = objectDesc.description || "";

            var object = new YMaps [constructor] (
				points, 
				{
					style: objectDesc.style, 
					hasBalloon : !!description, 
					draggable: true
				}
			);

            object.description = description;

            object.name = objectDesc.name;

            return object;
        }

        // import styles
        for (var i = 0; i < layer.styles.length; i++) 
		{
            YMaps.Styles.add (layer.styles [i].name, layer.styles [i].style);
        }

        // import objects
        for (var i = 0; i < layer.objects.length; i++) 
		{
            var o = createMapOverlay (layer.objects [i]);

            if (o) 
			{
                this.data[this.data.length] = o;
                this.map.addOverlay (o);

                // setup listeners for overlays
                if (o instanceof YMaps.Placemark) 
				{
                    this.addPlacemarkListener (o);
                } 
				else if (o instanceof YMaps.Polyline) 
				{
                    this.addLineObjectListener (o, this.polylineToolListener);
                } 
				else if (o instanceof YMaps.Polygon) 
				{
                    this.addLineObjectListener (o, this.polygonToolListener);
                }
            }
        }
    },

    // adds Editor listeners for YMaps.Placemark object
    addPlacemarkListener: function(placemark) 
	{
        var context = this;

        YMaps.Events.observe (placemark, placemark.Events.Click, function () {
            context.stopEditing ();
            context.showPlacemarkControl ();
            context.startEditing (placemark);
        });
    },

    // adds Editor listeners for YMaps.Polyline & YMaps.Poligon objects
    addLineObjectListener: function (overlay, toolListener) 
	{
        var context = this;

        YMaps.Events.observe (overlay, overlay.Events.Click, function () {
            context.stopEditing ();
            context.showLineControl ();
            context.startEditing (overlay);
        });

        YMaps.Events.observe (overlay, overlay.Events.StopEditing, function () {
            toolListener.disable ();
            context.setLineStyle ();
        });

        YMaps.Events.observe (overlay, overlay.Events.StopDrawing, function () {
            toolListener.enable ();
            context.setLineStyle ();
        });
    },

    // show PlacemarkSelectorControl pane & hides LineStyleControl pane  
    showPlacemarkControl: function () 
	{
        this.placemarkControl.show ();
        this.lineStyleControl.hide ();
        this.overlayName.focus ();
        this.overlayName.select ();
    },

    // hides PlacemarkSelectorControl pane & show LineStyleControl pane
    showLineControl: function () 
	{
        this.placemarkControl.hide ();
        this.lineStyleControl.show ();
        this.overlayName.focus ();
        this.overlayName.select ();
    },

    // constructs LineStyleControl DOM & adds it to the pane
    // setup change listeners
    addLineStyleControl : function() 
	{
        // construct pane HTML:
        out = '<div class="line">' + 
			'<div class="lineColor" style="width:185px;' +
			'background-color:#000000;height:10px"></div>';

        out += '<p>Цвет <select class="lineColorSelector" style="width:185px;">'

        var c = (['00', '80', 'ff']);

        for (var r = 0; r < c.length; r++)
		{
            for (var g = 0; g < c.length; g++)
			{
                for (var b = 0; b < c.length; b++) 
				{
                    color = c [r] + c [g] + c [b];
                    out += '<option value="' + 
						color + 
						'" style="background-color:#' + 
						color + 
						'">#' + 
						color + 
						'</option>';
                }
			}
		}

        out += '</select></p>';

        out += '<p>Толщина <select class="lineWidthSelector" style="width:185px;">'

        for (var i = 1; i <= 10; i++) 
		{
            out += '<option value="' + i + '" ">' + i + '</option>';
        }

        out += '</select></p>';

        out += '<p>Прозрачность <select class="lineAlphaSelector" style="width:185px;">'

        c = (['20', '40', '60', '80', 'a0', 'c0', 'e0', 'ff']);

        for (var i = 0; i <= c.length; i++) 
		{
            var l = c [c.length - i]
            out += '<option value="' + 
				c[i] + 
				'" style="background-color:#' + l + l + l + 
				'">#' + (i + 1) + 
				'</option>';

        }

        out += '</select></p>';

        out += '</div>';

        YMaps.jQuery (out).appendTo (YMaps.jQuery ('#tools'));

        // store component markers
        this.lineStyleControl = YMaps.jQuery ('#tools .line');

        this.lineSample = YMaps.jQuery ('#tools .line .lineColor');

        this.colorSelector = YMaps.jQuery ('#tools .line .lineColorSelector');

        this.withSelector = YMaps.jQuery ('#tools .line .lineWidthSelector');

        this.alphaSelector = YMaps.jQuery ('#tools .line .lineAlphaSelector');

        // setup change listener
        var context = this;

        YMaps.jQuery ('#tools .line select').change (function () 
		{
            var style = {
                strokeColor: context.colorSelector.val () + 
					context.alphaSelector.val(),
                strokeWidth: context.withSelector.val ()
            };
            context.setLineStyle (style);
        });
    },

    // constructs PlacemarkSelectorControl DOM & adds it to the pane
    // setup change listeners
    addPlacemarkSelectorControl: function() 
	{

        // construct pane HTML:
        var h = (YMaps.jQuery ('#toolsBox').height () - 
			YMaps.jQuery('#tools').height () - 5);

        var out = '<div class="placemark" style="height:' + 
			h + 
			'px; overflow-y:scroll;margin:0;padding:0;">';

        for (var i = 0; i < this.predefinedStyles.length; i++) 
		{
            var s = this.predefinedStyles [i];

            out += '<div class="placemarkStyle" id="' + 
				s.style + 
				'" title="' + 
				s.style + '"><img src="' + 
				s.url + 
				'"/></div>';
        }

        out += '</div>';

        YMaps.jQuery (out).appendTo (YMaps.jQuery ('#tools'));

        // store component markers
        this.placemarkControl = YMaps.jQuery ('#tools .placemark');

        // setup change listeners
        var context = this;

        YMaps.jQuery ('#tools .placemark .placemarkStyle').click (function () 
		{
            context.setPlacemarkStyle (this.id);
        });
    },

    // setup change listeners for DescriptionEditorControl
    addDescriptionEditorControl: function() 
	{
        // store component markers
        this.descriptionEditorControl = YMaps.jQuery ('#tools .baloon');
        this.overlayName = YMaps.jQuery ('#tools .baloon .overlayName');
        this.overlayDescription = YMaps.jQuery ('#tools .baloon .overlayDescription');

        // setup change listeners
        var context = this;
        
		var handler = function () 
		{
            context.setDescription ();
        };

        this.overlayName.change (handler);

        this.overlayDescription.change (handler);
    },

    // setup action listeners for RemoverControl
    addRemoverControl: function() 
	{
        // store component markers
        this.removerControl = YMaps.jQuery ('#tools .remover');

        // setup change listeners
        var context = this;

        YMaps.jQuery ('#tools .remover .show').click (function () 
		{
            if (context.currentObject instanceof YMaps.Placemark) 
			{
                context.map.setCenter (context.currentObject.getGeoPoint ());
            } 
			else if (context.currentObject instanceof YMaps.Polyline) 
			{
                context.map.setCenter (context.currentObject.getPoint (0));
            } 
			else if (context.currentObject instanceof YMaps.Polygon) 
			{
                context.map.setCenter (context.currentObject.getPoint (0));
            }

        });

        YMaps.jQuery ('#tools .remover .remove').click (function () 
		{
            if (confirm ('Удалить объект?')) 
			{
                context.removerControl.hide ();
                context.stopEditing ();
                context.map.removeOverlay (context.currentObject);

                if (context.currentObject) 
				{
                    for (var i = 0; i < context.data.length; i++) 
					{
                        if (context.data [i] == context.currentObject) 
						{
                            context.data [i] = null;
                            break;
                        }
                    }

                    context.currentObject = null;
                }
            }
        });

        context.removerControl.hide();
    },

    // setup action listeners for LayerControl
    addLayerControl: function () 
	{
        // setup action listeners
        var context = this;

        YMaps.jQuery ('#tools .save').click (function () 
		{
            var name = YMaps.jQuery ('#tools .layerName').val ();

            if (confirm ('Сохранить слой "' + name + '"?')) 
			{
                var layer = context.exportLayer (name);

                var out = JSON.stringify (layer);

                //TODO// place you storing procedure here:

                YMaps.jQuery ('#tools .layerContent').val (out);

                //TODO// as sample: adding layer to layer viewer:

                layers.add (layer);

                layers.showLayerList ();
            }
        });
    },

    // adds placemark tool control
    addPlacemarkTool: function (toolbar) 
	{

        //create
        var tool = new YMaps.ToolBarRadioButton (YMaps.ToolBar.DEFAULT_GROUP, {
            icon: "http://api.yandex.ru/i/maps/tools/draw/add_point.png",
            width: 20,
            hint: 'Режим добавления меток'
        });

        toolbar.add (tool);

        var context = this;

        //handle events
        var listener = YMaps.Events.observe (
			this.map, 
			this.map.Events.Click, function (map, mEvent) {
				this.stopEditing();

				// adding new placemark
				var placemark = new YMaps.Placemark (mEvent.getGeoPoint (), {
					draggable: true,
					style: this.placemarkStyle,
					hasBalloon: false,
					hasHint: true
	            });

				placemark.description = this.overlayDescription.val ();

				placemark.name = this.overlayName.val ();

				map.addOverlay (placemark);

				// add to layer objects
				this.data [this.data.length] = placemark;

				this.startEditing (placemark);

				context.addPlacemarkListener (placemark);
			}, 
			this
		);

        listener.disable ();

        YMaps.Events.observe (tool, tool.Events.Select, function () {
            context.stopEditing ();
            context.showPlacemarkControl ();
            listener.enable ();
        }, toolbar);

        YMaps.Events.observe (tool, tool.Events.Deselect, function () {
            context.stopEditing ();
            listener.disable ();
        }, toolbar);
    },

    // helper for Polyline & Polygon tools
    // set description for object & setups listeners
    addLineObject: function (overlay, listener) 
	{
        var context = this;

        overlay.description = this.overlayDescription.val ();
        overlay.name = this.overlayName.val ();
        overlay.setEditingOptions ({drawing:true});

        this.map.addOverlay (overlay);

        // events
        listener.disable ();

        this.addLineObjectListener (overlay, listener);

        // add to layer objects
        this.data [this.data.length] = overlay;

        this.startEditing (overlay);
    },

    // adds polyline tool control
    addPolylineTool: function (toolbar) 
	{
        var tool = new YMaps.ToolBarRadioButton (YMaps.ToolBar.DEFAULT_GROUP, {
            icon: "http://api.yandex.ru/i/maps/tools/draw/add_line.png",
            width: 20,
            hint: 'Режим добавления ломанных линий'
        });

        toolbar.add(tool);

        var context = this;

        //handle events
        this.polylineToolListener = YMaps.Events.observe (
			this.map, 
			this.map.Events.Click, function (map, mEvent) {
				this.stopEditing();
            
				// adding new polyline
				var overlay = new YMaps.Polyline ([
					mEvent.getGeoPoint()
				], 
				{
					style: context.createStyle(),
					draggable: true,
					hasBaloon: false,
					hasHint: true
				});

				this.addLineObject (overlay, this.polylineToolListener);
			}, 
			this
		);

        this.polylineToolListener.disable ();

        YMaps.Events.observe (tool, tool.Events.Select, function () {
            context.stopEditing ();
            context.showLineControl ();
            context.polylineToolListener.enable ();
        }, toolbar);

        YMaps.Events.observe (tool, tool.Events.Deselect, function () {
            context.stopEditing ();
            context.polylineToolListener.disable ();
        }, toolbar);
    },

    // adds polygon tool control
    addPolygonTool: function (toolbar) 
	{
        var tool = new YMaps.ToolBarRadioButton (YMaps.ToolBar.DEFAULT_GROUP, {
            icon: "http://api.yandex.ru/i/maps/tools/constructor/add_polygon.png",
            width: 20,
            hint: 'Режим добавления многоугольников'
        });

        toolbar.add(tool);

        var context = this;

        //handle events
        this.polygonToolListener = YMaps.Events.observe (
			this.map, 
			this.map.Events.Click, function (map, mEvent) {
				this.stopEditing();

				// adding new polygon
				var overlay = new YMaps.Polygon ([
					mEvent.getGeoPoint()
				], 
				{
					style: context.createStyle (),
					draggable: true,
					hasBaloon: false,
					hasHint: true
				});
				
				this.addLineObject(overlay, this.polygonToolListener);
			}, 
			this
		);

        this.polygonToolListener.disable ();

        YMaps.Events.observe (tool, tool.Events.Select, function () {
            context.stopEditing ();
            context.showLineControl ();
            context.polygonToolListener.enable ();
        }, toolbar);

        YMaps.Events.observe (tool, tool.Events.Deselect, function () {
            context.stopEditing ();
            context.polygonToolListener.disable ();
        }, toolbar);
    }
{/literal}