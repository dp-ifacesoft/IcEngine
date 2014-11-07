/**
 * 
 * @desc Контроллер яндекс карт.
 * @package IcEngine
 * 
 */
var Controller_Yandex_Map = {
	
	/**
	 * @desc Скрипт для ленивой загрузки яндекс карт
	 * @var string
	 */
	lazyLoadScript: null,
	
	/**
	 * @desc Ленивая загрузка яндекс карт
	 */
	lazyLoad: function ()
	{
		if (typeof YMaps == 'undefined' && this.lazyLoadScript)
		{
			$(document).append ('<script src="' + this.lazyLoadScript + '" type="text/javascript"></script>');
		}
		this.waitLoad ();
	},
	
	/**
	 * @desc Инициализация яндекс карты
	 */
	waitLoad: function ()
	{
		if (typeof YMaps != 'undefined')
		{
			if (typeof YMaps.Map == 'undefined')
			{
				YMaps.load ();
			}
		}
		else
		{
			setTimeout (
				function () { Controller_Yandex_Map.waitLoad (); },
				500
			);
		}
	}
	
};