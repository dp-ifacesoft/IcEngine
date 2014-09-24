/**
 * @desc Базовый класс контроллера
 */
var Controller = {

	_callbacks: {},

	_callbacksData: {},

	_lastback: 0,

	_slowTimer: null,

	_transports: [],

	/**
	 * @desc Вызов метода контроллера на сервере
	 * @param controller
	 * @param params
	 * @param callback
	 * @param nocache
	 */
	call: function (controller, params, callback, nocache)
	{
		var back = Controller._lastback++;
		Controller._callbacks [back] = callback;
		Controller._callbacksData [back] = params ['back'] ? params ['back'] : null;
		if (!Controller._transports.length)
		{
			Controller._transports.push (JsHttpRequest);
		}
		var transport = Controller._transports [Controller._transports.length - 1];
		var targetUrl;
		if (!('targetUrl' in transport))
		{
			targetUrl = '/Controller/ajax/';
		}
		else
		{
			targetUrl = transport.targetUrl;
		}
		transport.query (
			targetUrl,
			{
				'call': controller,
				'params': params,
				'back': back
			},
			Controller.callback,
			nocache ? true : false
		);
	},

    /**
     * @desc Однократный вызов метода контроллера на сервере
     * @param controller
     * @param params
     * @param callback
     * @param nocache
     */

    callOnce: function(controller, params, callback, nocache)
    {
        Controller.call(controller, params, callback, nocache);
        /**
         * implementation fas bug. First fix
         */
//        if (controller in Controller._callOnes)
//        {
//            return false;
//        }
//        var back = Controller._lastback;
//        Controller._callOnceControllers[back] = controller;
//        Controller._callOnes[controller] = back;
//        Controller.call(controller, params, callback, nocache);
    },

	/**
	 * @desc Ответ сервера
	 * @param object js
	 * @param string text
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

		var callback = Controller._callbacks [back];
		js.result.back =
			Controller._callbacksData [back] ?
			Controller._callbacksData [back] : null;

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
			Controller.call (
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
	 * @param controller
	 * @param params
	 * @param callback
	 * @param nocache
	 * @param integer delay Задержка в милисекундах перед вызовом
	 */
	slowCall: function (controller, params, callback, nocache, delay)
	{
		if (Controller._slowTimer)
		{
			clearTimeout (Controller._slowTimer);
		}

		Controller._slowTimer = setTimeout (
			function ()
			{
				Controller._slowTimer = null;
				Controller.call (controller, params, callback, nocache);
			},
			delay
		);
	}

};