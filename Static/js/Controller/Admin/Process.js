/**
 *
 * @desc Controller for process controlling
 * @author Yury Shvedov
 * @package IcEngine
 * 
 */
var Controller_Admin_Process = {
	
	/**
	 * @desc Изменение состояния
	 * @param model string
	 * @param key string
	 * @param field string
	 * @param status integer
	 * @param where DOMElement
	 */
	change: function (where, model, key, field, status)
	{
		if (where)
		{
			$(where).closest ('div').remove ();
		}
		
		function callback (result)
		{
			if (result.data && result.data.title)
			{
				$('.status__' + model + '_' + key).html (result.data.title);
			}
		}
		
		Controller.call (
			'Admin_Process/change',
			{
				model: model,
				key: key,
				field: field,
				status: status
			},
			callback, true
		);
	},
	
	/**
	 * @desc
	 * @param where DOMElement
	 * @param model string
	 * @param key string
	 * @param field string
	 */
	choose: function (where, model, key, field)
	{
		var $where = $(where);
		var offset = $where.offset ();
		
		var html = '<div style="position:absolute; padding: 4px; border: 1px solid black; background: #EAE7E2; left:' + offset.left + 'px; top: ' + offset.top + 'px;">';
		
		var items = [
			{id: 0, title: 'none'},
			{id: 1, title: 'ongoing'},
			{id: 2, title: 'fail'},
			{id: 3, title: 'success'},
			{id: 4, title: 'pause'},
			{id: 5, title: 'stoped'}
		];
		
		for (var i in items)
		{
			html += '<a href="javascript:void(0);" ' +
				'onclick="Controller_Admin_Process.change (this, \'' + model + '\', \'' + key +'\', \'' + field + '\', ' + items [i].id + ');">' +
				items [i].title +
				'</a><br />'
		}
		
		html += '</div>';
		
		$('body').append (html);
	}
	
};