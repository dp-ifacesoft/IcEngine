/**
 * 
 * @desc Контроллер для администрирования БД
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
var Controller_Admin_Database = {
	
	/**
	 * @desc Выбор всех полей в таблице
	 * @param $sender jQuery
	 * @param value_filter string
	 */
	multiCheck: function ($sender, value_filter)
	{
		var $checkboxes = $('input[type=checkbox][name^="resources"][value^="' + value_filter + '"]');
		$checkboxes.attr ('checked', $sender.is(':checked'));
	}
	
};