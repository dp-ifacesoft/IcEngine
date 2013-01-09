/**
 * 
 * @desc Рендер представлений.
 * @author Yury Shvedov, Ilya Kolesnikov
 * @package IcEngine
 * 
 */
var View_Render = {
	
	/**
	 * @desc Переменные шаблонизатора
	 * @var object
	 */
	tplVars: {
		'is_js': true
	},
	
	/**
	 * @desc Шаблоны
	 * @var object
	 */
	templates: {
		'empty': ""
	},
	
	/**
	 * @desc Установить значение шаблона.	
	 * @param key Массив значений или имя переменной.
	 * @param value [optional] Значение переменной (если первый параметр
	 * строка - имя переменной)
	 */
	assign: function (key, value)
	{
		if (typeof key == 'object')
		{
			$.extend (View_Render.tplVars, key);
		}
		else
		{
			View_Render.tplVars [key] = value;
		}
	},
	
	/**
	 * @desc Компилировать шаблон в строку
	 * @param tpl Название шаблона.
	 * @return string
	 */
	fetch: function (tpl)
	{
		if (!View_Render.templates [tpl])
		{
            console.log(tpl);
			Debug.echo ('No template: ', tpl);
			return null;
		}
		
		return Helper_Render_Smarty.processDOMTemplate (
			View_Render.templates [tpl],
			View_Render.tplVars
		);
	},
	
	/**
	 * @desc Получить значение переменной из шаблона
	 * @param key Название переменной.
	 * @return Значение переменной.
	 */
	getVar: function (key)
	{
		return View_Render.tplVars [key];
	}
	
};
