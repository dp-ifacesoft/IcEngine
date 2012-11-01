/**
 * Помощник работы с формой.
 */
var Helper_Form = {

	/**
	 * Последняя форма, на которой запущен лоадинг
	 */
	$lastLoadings: null,

	_toggleTextForPassword: function ($password)
	{
		if ($password.data ('tps_input'))
		{
			return $password.data ('tps_input');
		}

		var elPosition = $password.position ();
		var $input = $('<input/>', {
	    	'class': "hidden",
	    	css: {
	    		display: 'none',
	    		left: elPosition.left,
	    		top: elPosition.top
	    	},
	    	type: 'text'
	    });

		$input.data ('tps_password', $password);
		$password.data ('tps_input', $input);
		$input.appendTo ($password.parent ());

		return $input;
	},

	ajaxPost: function ($form, url, callback)
	{
		var data = Helper_Form.asArray ($form);

		JsHttpRequest.query (url, data, callback, true);
	},

	/**
	 * @desc Все поля формы как поля объекта
	 * @param jQuery $form Форма.
	 * @param string filter jQuery selector.
	 * @param boolean check проверять ли required
	 * @returns object Объект, содержащий данные с формы.
	 */
	asArray: function ($form, filter, check)
	{
		$fields =
			$form.find ('input,select,textarea')
			.filter (':not(.nosubmit)');

		if (filter)
		{
			$fields.filter (filter);
		}
		if (!check) {
			check = false;
		}
		var data = {}, placeholder, value,
			errorRequired = false;

		$fields.each (function () {

            /**
             * для корректной обработки select:multiple и
			 * параметров с именем "param[]"
             *
			 * @author red
             */
            function _setValue (name, value, lastObject, attrName) {
                var _name = name;
                var _isArray = false;
				var _isObject = false;
				var i;

                if (name.slice(-2) == '[]') {
                    _name = name.slice(0, -2);
                    _isArray = true;
                } else if (typeof value == 'object') {
                    _isArray = true;
                } else if (/\[([^\]]+)\]/.test(name)) {
					_isObject = true;
				} else if (lastObject) {
					_isObject = true;
				}

                if (_isArray) {
                    if (!(_name in data)) {
                        data[_name] = [];
                    }
                    if (typeof value == 'object' ) {
                        for (i in value) {
                            if (typeof value == 'string') {
                                data[_name].push (value[i]);
                            }
						}
                    } else {
                        data[_name].push (value);
                    }
                } else if (_isObject) {
					var pos;
					if (!lastObject) {
						pos = name.indexOf('[');
						_name = name.substr(0, pos);
						name = name.substr(pos);
						if (!(_name in data)) {
							data[_name] = {};
						}
						_setValue(name, value, data[_name]);
					} else {
						if (name) {
							pos = name.indexOf('[');
							var endPos = name.indexOf(']');
							_name = name.substr(pos + 1, endPos - pos - 1);
							attrName = null;
							if (endPos + 1 < name.length) {
								name = name.substr(endPos + 1);
							} else {
								name = '';
								attrName = _name;
							}
							if (!(_name in lastObject)) {
								lastObject[_name] = {};
							}
							if (!attrName) {
								_setValue(name, value, lastObject[_name]);
							} else {
								_setValue(name, value, lastObject, _name);
							}
						} else {
							lastObject[attrName] = value;
						}
					}
				} else {
                    data[_name] = value;
                }
            }


			if (this.tagName.toLowerCase () == 'input')
			{
				if (this.type == "file")
				{
					_setValue (this.name, this);
				}
				else if (this.type == 'checkbox')
				{
					if (this.checked)
					{
						_setValue (this.name, $(this).val() ? $(this).val() : 'on');
					}
				}
				else if (this.type == 'radio')
				{
					if (this.checked)
					{
						_setValue (this.name, $(this).val ());
					}
				}
				// обычные input[name=text]
				else
				{
					if(check && $(this).attr('required') && $(this).is(':visible'))
					{
						value = $(this).val();
						placeholder = $(this).attr('placeholder');
						if(!value.length || value == placeholder)
						{
							$(this).addClass('errorRequired');
							$(this).attr('required');
							errorRequired = true;
						}
						else
						{
							$(this).removeClass('errorRequired');
						}
					}
					if ($(this).attr ('placeholder') == $(this).val ())
					{
						_setValue (this.name, '');
					}
					else
					{
						_setValue (this.name, $(this).val ());
					}
				}
			} else {
				//можно на наследование переделать всё это, вверху input ещё
				if (check && this.tagName.toLowerCase () == 'textarea') {
					if ($(this).attr('required')) {
						if ($(this).is(':visible')) {
							if ($(this).val() == '') {
								$(this).addClass('errorRequired');
								errorRequired = true;
							} else {
								$(this).removeClass('errorRequired');
							}
						} else {
							if ($(this).attr('id') &&
								$('#' + $(this).attr('id') + '_tbl').length &&
								$('#' + $(this).attr('id') + '_tbl').is(':visible')) {
									if (Controller_TinyMce.getVal({'id': $(this).attr('id')}) == '') {
										$('#' + $(this).attr('id') + '_tbl')
											.addClass('errorRequired');
										errorRequired = true;
									} else {
										$('#' + $(this).attr('id') + '_tbl')
											.removeClass('errorRequired');
									}
								}
						}

					}
				}
				if (this.tagName.toLowerCase() == 'select') {
					if($(this).attr('required')) {
						value = $(this).val();
						if (!value || value == 0) {
							$(this).addClass('errorRequired');
							errorRequired = true;
						} else {
							$(this).removeClass('errorRequired');
						}
					}
				}
				if ($(this).attr ('placeholder') == $(this).val ()) {
					_setValue (this.name, '');
				} else {
					_setValue (this.name, $(this).val ());
				}
			}
		});
		data['errorRequired'] = errorRequired;
		return data;
	},

	/**
	 * Тоже самое что и asArray, но с проверкой Required полей
	 * по-хорошему надо бы перепилить asArray, но тогда на всём сайте
	 * все формы править
	 */
	asArrayWCheck: function ($form, filter)
	{
		var result;
		result = this.asArray ($form, filter, true);
		return !result['errorRequired'] ? result : false;
	},

	defaultCallback: function ($form, result)
	{
		if (!result)
		{
			Helper_Form.stopLoading ($form);
			return;
		}

		if (result.data && result.data.alert)
		{
			alert (result.data.alert);
		}

		if (result.html)
		{
			if (result.data && result.data.removeForm)
			{
				$form.parent ().html (result.html);
			}
			else
			{
				if (result.data && result.data.error && result.data.field)
				{
					$('[name=' + result.data.field + ']').addClass ("err");
				}

				// последнее вхождение ".result-msg", позволяет
				// подменять нижнюю часть формы, которая будет содержать
				// новый .result-msg
				var $div = $form.find ('.result-msg');
				if (!$div.length)
				{
					$div = $form.parent ().find ('.result-msg');
				}
				var $subdiv = $div.find ('.result-msg');
				while ($subdiv.length)
				{
					$div = $subdiv;
					$subdiv = $div.find ('.result-msg');
				}

				$div.html (result.html);
				$div.show ();
				Helper_Form.stopLoading ($form);
			};
		}
		else
		{
			if (result.data.removeForm)
			{
				$form.parent ().remove ();
			}
		}

		if (result.data && result.data.redirect)
		{
			setTimeout (
				function () {
					window.location.href = result.data.redirect;
				},
				1000
			);
		};

		if (typeof (Controller_Captcha) != "undefined")
		{
			Controller_Captcha.regenerateACodes ($form);
		}
	},

	/**
	 * Отправка формы по умолчанию
	 *
	 * @param $form jQuery Форма или элемент формы.
	 * @param action string Название контроллера и экшена.
	 */
	defaultSubmit: function($form, action) {
		var $e = $form;
		$form = $form.closest('form');
		var data = {};
		var callback = function(result) {
			Helper_Form.defaultCallback($form, result);
		};
		var p1;
		if ($form.length) {
			data = Helper_Form.asArray($form);
			if (!action) {
				action = $form.attr('onsubmit');
				p1 = action.indexOf('(');
				var p2 = action.indexOf(' ');
				action = action.substring (
					"Controller_".length,
					(0 < p2 && p2 < p1) ? p2 : p1
				);
				action = action.replace(".", "/");
			}
		} else {
			var funcName = $e.attr('onclick');
			p1 = funcName.indexOf('(');
			action = funcName.substring("Controller_".length, p1);
			action = action.replace('.', '/');
			callback = function(result) {

			};
		}
		Controller.call(action, data, callback, true);
	},

	/**
	 *
	 * @param jQuery $owner
	 * @returns jQuery input[type=file]
	 */
	initFileUpload: function ($owner)
	{
		var $input = $('input[type="file"]', $owner);
		var is_new = false;

		if ($input.length == 0)
		{
			$input = $('<input type="file" />');
			is_new = true;
		};

		$input.css ({
			position: "absolute",
			opacity: 0.0,
			cursor: "pointer",
			display: "inherit",
			margin: 0,
			padding: 0,
			width: "220px"
		});

		if (is_new)
		{
			$owner.append ($input);
		};

		$owner.data ('input_file_upload', $input);
		$owner.css ('overflow', 'hidden');

		var pos = $owner.css ('position');
		if (pos != "relative" && pos != "absolute")
		{
			$owner.css ('position', 'relative');
		};

		$owner.bind ('mousemove', function (event) {
			var $input = $(event.currentTarget).data ('input_file_upload');

			var y = event.pageY - $owner.offset ().top - 5 + 'px';
			var x = event.pageX - $owner.offset ().left - 170 + 'px';

			$input.css ({left: x, top: y});
		});

		return $input;
	},

	initTogglePasswordStars: function ($from)
	{
		if (!$from)
		{
			$form = $('body');
		}

		$checkboxes = $('input[type="checkbox"].tps_checkbox', $from);

		$checkboxes.each (function () {
			$(this).bind (
				'click',
				function ()
				{
					var $this = $(this);

					if ($this.data ('tps_text'))
					{
						Helper_Form.togglePasswordStars ($this, $this.data ('tps_text'));
						return;
					}

					var $parent = $this.parent ();
					while ($parent.length)
					{
						var $tps_password = $parent.find ('.tps_password');

						if ($tps_password.length)
						{
							var $tps_input = Helper_Form._toggleTextForPassword ($tps_password);
							$this.data ('tps_password', $tps_password);
							$this.data ('tps_input', $tps_input);
							Helper_Form.togglePasswordStars ($this);
							return;
						}
						$parent = $parent.parent ();
					}
				}
			);
		});
	},

	/**
	 * Генерация уникальных ID для элементов
	 * Необходимо для чекбоксов в IE: если 2 чекбокса имеют одинаковый id,
	 * значение checked возможно установить только для первого.
	 * @param $collection
	 */
	initUniqueIds: function ($collection)
	{
		var m = 0;
		$collection.each (function () {
			m++;
			var id = "u" + new Date ().getTime () + "m" + m;
			$(this).attr ('id', id);
		});
	},

	/**
	 * Заменить элементы управления на див загрузки
	 * @param jQuery $controls
	 */
	startLoading: function ($controls)
	{
		Helper_Form.$lastLoadings = $controls;
		$controls.each (function () {
			$(this).append ('<div class="loading"></div>');
		});
	},

	/**
	 * Окончание процесса загрузки
	 * @param jQuery $form
	 */
	stopLoading: function ($form)
	{
		if (!$form)
		{
			$form = Helper_Form.$lastLoadings;
		}

		if (
			typeof ($form) != 'object' || $form == null ||
			typeof ($form.find) != 'function'
		)
		{
			return ;
		}

		$form.find ('div.loading').remove ();

		Helper_Form.$lastLoadings = null;
	},

	/**
	 * Показать/скрыть пароль за звездочками
	 *
	 * Если не передан второй атрибут, то его id должен быть равен
	 * "tps_%id_чекбокса%".
	 *
	 * Если первый атрибут null, второй атрибут обязателен. Состояние
	 * поля будет изменено на противоположное.
	 */
	togglePasswordStars: function ($checkbox, $input)
	{
		var checked;
		var $tps_input;
		var $tps_password;

		if ($checkbox)
		{
			$tps_input = $checkbox.data ('tps_input');
			$tps_password = $checkbox.data ('tps_password');
			checked = $checkbox.attr ('checked');
		}
		else
		{
			checked = $input.attr ('type') == 'password';
			if (checked)
			{
				$tps_password = $input;
				$tps_input = Helper_Form._toggleTextForPassword ($tps_password);
			}
			else
			{
				$tps_input = $input;
				$tps_password = $input.data ('tps_password');
			}
		};

		if (checked)
		{
			$tps_password.val ($tps_input.val ()).show ();
			$tps_input.hide ();
		}
		else
		{
			$tps_input.val ($tps_password.val ()).show ();
			$tps_password.hide ();
		}
	}

};