/**
 * @desc Базовый класс контроллера
 */
Ice.Controller = {
		
	_callbacks: {},
	
	_callbacksData: {},
	
	_lastback: 0,
	
	_slowTimer: null,
	
	/**
	 * @desc Вызов метода контроллера на сервере
	 * @param controller string
	 * @param params object
	 * @param callback function
	 * @param nocache boolean
	 */
	call: function (controller, params, callback, nocache)
	{
		var back = Ice.Controller._lastback++;
		Ice.Controller._callbacks [back] = callback;
		Ice.Controller._callbacksData [back] = params ['back'] 
			? params ['back'] 
			: null;
			
		JsHttpRequest.query (
			'/Controller/ajax/',
			{
				'call': controller,
				'params': params,
				'back': back
			},
			Ice.Controller.callback,
			Boolean (nocache)
		);
	},
	
	/**
	 * @desc Ответ сервера
	 * @param js object
	 * @param text string
	 */
	callback: function (js, text)
	{
		var back = js.back;
		
		if (!js.result || js.error)
		{
			if (console && console.log)
			{
				console.log ('error_js:', js);
			}
			Helper_Form.stopLoading ();
			return;
		}
		
		var callback = Ice.Controller._callbacks [back];
		js.result.back = 
			Ice.Controller._callbacksData [back] ? 
			Ice.Controller._callbacksData [back] : null;
		
		callback (js.result, text);
	},
	
	removeSelected: function (item)
	{
		var ids = {};
		var n = 0;
		var l = (item + '_del_').length;
		$('input[type="checkbox"]:checked.' + item + '_del').each (
			function () {
				ids [n] = $(this).attr ('name').substr (l);
				n++;
			}
		);
		
		function callback (result)
		{
			if (result.code == 200)
			{
				alert ("Удалено: " + result.count);
			}
			else if (result.msg)
			{
				alert (result.msg);
			}
		}
		
		if (confirm ("Будет удалено " + n + " позиций.\nПродолжить?"))
		{
			Ice.Controller.call (
				item + '/remove',
				{
					"ids": ids
				},
				callback, true
			);
		}
	},
	
	/**
	 * @desc Вызов экшена с указанной задержкой. Предотвращает
	 * многократный вызов одного экшена.
	 * @param controller string
	 * @param params object
	 * @param callback function
	 * @param nocache boolean
	 * @param delay integer Задержка в милисекундах перед вызовом
	 */
	slowCall: function (controller, params, callback, nocache, delay)
	{
		if (Ice.Controller._slowTimer)
		{
			clearTimeout (Ice.Controller._slowTimer);
		}
		
		Ice.Controller._slowTimer = setTimeout (
			function ()
			{
				Ice.Controller._slowTimer = null;
				Ice.Controller.call (controller, params, callback, nocache);
			},
			delay
		);
	}
		
};