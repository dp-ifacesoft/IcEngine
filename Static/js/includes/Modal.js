var Modal = {
	options: {},
	show : function(options){
		Modal.options = jQuery.extend({
			opacity : 0.5 // прозрачность подложки
			,width : false // ширина контейнера
			,height : false // высота контейнера
			,maxWidth : false // максимальная ширина контейнера
			,maxHeight : false // максимальная высота контейнера
			,speed : 300 // скорость появления
			,addClass : false // css класс для модального окна
			,style : false // стили контейнера
			,overlay : true // показывать ли подложку
			,elem : false // элемент, передаваемый в контейнер
			,cloneElem : true // если true, то элемент не удаляется
			,html : false // html, передаваемый в контейнер
			,title : false // текст заголовка
			,titleCss : false // стили заголовка
			,hideOnEsc : true // прятать ли по нажатию клавиши ESC
			,closeBtn : true // показывать ли кнопку закрытия окна
			,closeBtnText : '' // текст кнопки закрытия окна
			,fixed : false
			,beforeShow : function(){} // действие перед появлением модального окна
			,afterShow : function(){} // действие после появления модального окна
			/**
			 * @desc Действие после вызова метода success у модального окна
			 * @var function
			 */
			,onSuccess: function () {}
			/**
			 * @desc Действие после вызова метода success у модального окна
			 * и отправкой Model.hide() этому окну.
			 * @var function
			 */
			,afterSuccess: function () {} 
			,afterHide: function (){} // действие после скрытия формы
		},options);
		
		document.body.style.height = 'auto';
		
		if (typeof Modal.options.beforeShow == 'function')
		{
			Modal.options.beforeShow.call();
		}

		var  ie6 = ($.browser.msie && parseInt($.browser.version) < 7) ? true : false;

		var setPosition = (function(container){
			var top;
			if (ie6)
			{
				top = Math.round(windowTop() + (windowHeight()/2) - container.height()/2);
			}
			else
			{
				top = !Modal.options.fixed ? Math.round(windowHeight()/2 - container.height()/2) : Math.round(windowTop() + (windowHeight()/2) - container.height()/2);
			}
			if ((ie6 || Modal.options.fixed) && top < windowTop())
			{
			    top = windowTop();
			}
			var left = Math.round(windowWidth()/2 - container.width()/2);
			container.css({'top' : top, left : left});
		});

		var windowHeight = (function(){
			return document.compatMode=='CSS1Compat' ? document.documentElement.clientHeight : document.body.clientHeight;
		});

		var windowWidth = (function(){
			return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;
		});

		var windowTop = (function(){
			return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
		});

		var windowBottom = (function(){
			return windowTop() + windowHeight();
		});

		var windowLeft = (function(){
			return self.pageXOffset || (document.documentElement && document.documentElement.scrollLeft) || (document.body && document.body.scrollLeft);
		});

		var windowRight = (function(){
			return windowLeft() + windowWidth();
		});

		var opacity = Modal.options.opacity ? Modal.options.opacity : 0.5;
		var speed = Modal.options.speed ? Modal.options.speed : 0;

		var overlay = null;
		var container = null;
		var content = null;
		var $close = null;

		var count = $('div[id^=aud_modal_overlay-]').length > 0 ? parseInt($('div[id^=aud_modal_overlay-]').attr('id').split('-')[1]) + 1 : 0;

		overlay = jQuery('<div id="aud_modal_overlay-'+count+'"></div>');

		container = '<div id="aud_modal_container-'+count+'">';
		container += Modal.options.title ? '<h1 id="aud_modal_title-'+count+'" style="margin-bottom:8px;">'+Modal.options.title+'</h1>' : '';
		container += '</div>';
		container = jQuery(container);

		content = '<div>';
		content += '</div>';
		content = jQuery(content);

		if (Modal.options.closeBtn)
		{
			$close = '<span id="aud_modal_close">';
			$close += Modal.options.closeBtnText ? Modal.options.closeBtnText : '';
			$close += '</span>';
			$close = jQuery($close);
		}

		var position = !Modal.options.fixed ? 'fixed' : 'absolute';
		var overlay_position = 'fixed';
		var height = '100%';

		if (ie6)
		{
			position = 'absolute';
			overlay_position = 'absolute';
			height = document.body.scrollHeight > document.body.offsetHeight ? document.body.scrollHeight : document.body.offsetHeight + "px";
		}

		if (Modal.options.overlay)
		{
			overlay
				.css({
					'position' : overlay_position
					,'z-index' : 9999999
					,'top' : 0
					,'left' : 0
					,'height' : height
					,'width' : '100%'
					,'background-color' : '#000'
					,'opacity' : 0
				})
				.appendTo('body')
				.animate({opacity : opacity}, speed)
				.bind('click', function(){
					Modal.hide();
				});
		}

		if (Modal.options.width && parseInt(Modal.options.width) > 0)
		{
			container.width(Modal.options.width);
		}
		if (Modal.options.height && parseInt(Modal.options.height) > 0)
		{
			container.height(Modal.options.height);
		}
		container
			.css({
				'position' : position
				,'z-index' : 9999999
				,'opacity' : 0
				,'padding' : '8px'
				,'background-color' : '#FFF'
			})
			.appendTo('body')
			.animate({opacity : 1}, speed, function(){
				if (typeof Modal.options.afterShow == 'function')
				{
					Modal.options.afterShow.call($(this));
				}
			});

		if (Modal.options.addClass)
		{
		    container.addClass(Modal.options.addClass);
		}

		if (Modal.options.style)
		{
			if (typeof Modal.options.style == 'string')
			{
				Modal.options.style = Modal.options.style.replace(/(\s)/g,'');
				var style = Modal.options.style.split(';');
				var cssArr = {};
				for (i=0; i<style.length; i++)
				{
					if(/./.test(style[i]))
					{
						var preCss = style[i].split(':');
						cssArr[preCss[0]] = preCss[1];
					}
				}
			}
			else
			{
				var cssArr = Modal.options.style;
			}

			container.css(cssArr);
		}

		if (Modal.options.titleCss)
		{
			if (typeof Modal.options.titleCss == 'string')
			{
				Modal.options.titleCss = Modal.options.titleCss.replace(/(\s)/g,'');
				var style = Modal.options.titleCss.split(';');
				var cssArr = {};
				for (i=0; i<style.length; i++)
				{
					if(/./.test(style[i]))
					{
						var preCss = style[i].split(':');
						cssArr[preCss[0]] = preCss[1];
					}
				}
			}
			else
			{
				var cssArr = Modal.options.titleCss;
			}
			container.find('h1[id^=aud_modal_title-]').css(cssArr);
		}

		content
			.css({
				'position' : 'relative'
				,'height' : '100%'
				,'width' : '100%'
			}).appendTo(container);

		if (Modal.options.closeBtn)
		{
			$close
				.css({
					'background-image' : 'url("/images_site/dialog/dlg_close_btn.gif")'
					,'background-repeat' : 'no-repeat'
					,'background-position' : 'top right'
					,'color' : '#c4c2c2'
					,'cursor' : 'pointer'
					,'display' : 'block'
					,'font-family' : 'Trebuchet MS'
					,'font-size' : '13px'
					,'height' : '13px'
					,'padding-right' : '15px'
					,'line-height' : '12px'
					,'position' : 'absolute'
					,'right' : '8px'
					,'top' : '8px'
				})
				.bind('click', function(){
					Modal.hide();
				})
				.appendTo(container);
		}

		if (Modal.options.elem)
		{
			var elem = $(Modal.options.elem);
			if (Modal.options.cloneElem)
			{
				elem.clone(true).appendTo(content).show();
			}
			else
			{
				elem.appendTo(content).show();
			}
		}
		else if (Modal.options.html)
		{
			content.html(Modal.options.html);
		}

		// переназначаем ширину, если она больше максимально допустимой
		if (Modal.options.maxWidth && Modal.options.maxWidth > 0 && container.width() > Modal.options.maxWidth)
		{
			container.css('width',Modal.options.maxWidth);
		}

		// переназначаем высоту, если она больше максимально допустимой
		if (Modal.options.maxHeight && Modal.options.maxHeight > 0 && container.height() > Modal.options.maxHeight)
		{
			container
				.css({
					'height' : Modal.options.maxHeight
					,'padding-right' : 0
				});

			content
				.css({
					'overflow-y' : 'auto'
				});
		}

		// позиционируем блок в центре окна
		setPosition(container);

		// скрываем по нажатию ESC, если не запрещено в настройках
		if (Modal.options.hideOnEsc)
		{
			jQuery(document).bind('keyup',function(event){
				if (event.keyCode == 27)
				{
					Modal.hide({speed : speed});
				}
			});
		}
		// позиционируем блок по центру при изменении размера окна
		$(window).resize(function(){
			setPosition(container);
		});

		$(window).scroll(function(){
		    if (ie6 && !Modal.options.fixed)
		    {
		    	setPosition(container);
		    } 
		});
	},
	
	/**
	 * @desc Следует вызывать в случае подтверждения операции.
	 * @param extra object
	 */
	success: function (extra)
	{
		if (typeof Modal.options.onSuccess == 'function')
		{
			Modal.options.onSuccess (extra);
		}
		Modal.hide ();
		if (typeof Modal.options.afterSuccess == 'function')
		{
			Modal.options.afterSuccess (extra);
		}
	},
	
	hide : function ()
	{
		Modal.options = jQuery.extend({
			speed : 300
		},Modal.options);
		
		var speed = Modal.options.speed ? Modal.options.speed : 0;
		var overlay = $('div[id^=aud_modal_overlay]:last');
		var container = $('div[id^=aud_modal_container]:last');
		overlay.animate({opacity : 0}, speed, function(){
			$(this).remove();
		});

		container.animate({opacity : 0}, speed, function(){
			$(this).remove();

			if (typeof Modal.options.afterHide == 'function')
			{
				Modal.options.afterHide.call ();
			}
		});
	}
};