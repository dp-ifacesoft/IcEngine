var Controller_Data_Provider = {
	
	getValue: function (key, target)
	{
		
		function callback(result)
		{
			var inp_id = 'nv' + new Date ().getTime ();
			target.html (
				'<input type="text" id="' + inp_id + '" value="' + result.value.replace (/"/g, '&quot;') + '" style="width: 1000px;"/>' +
				'<input type="button" onclick="set_cacher_value(\'' + key + '\', $(\'#' + inp_id + '\').val(), $(this).parent());" value="set" />' 
			);
		}
		
		Controller.call (
			'Data_Provider/getValue',
			{
				key: key
			},
			callback, false
		);
	},

	setValue: function (key, value, target)
	{
		
		function callback (result)
		{
			target.html (result.value);
		}
		
		if (confirm (key + ' => ' + value))
		{
			Controller.call (
				'Data_Provider/setValue',
				{
					key: key,
					value: value
				},
				callback, false
			);
		}
	},

	remove: function (key, container)
	{
		
		function callback (result)
		{
			if (result.ok)
			{
				container.remove ();
			}
		}
		
		if (confirm ('Delete ' + key + '?'))
		{
			Controller.call (
				'Data_Provider/remove',
				{
					key: key
				},
				callback, false
			);
		}
	},

	flush: function ()
	{
		function callback ()
		{
			window.location.href = window.location.href;
		}
		
		if (confirm ('Delete all founded??'))
		{
			var pattern = $('#cache_pattern').val ();
			Controller.call (
				'Data_Provider/flush',
				{
					i: 2,
					pattern: pattern
				},
				callback, false
			);
		}
	}

	
};