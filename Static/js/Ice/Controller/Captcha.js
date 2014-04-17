/**
 *
 * @desc Каптча
 * @package IcEngine
 * 
 */
Ice.Controller_Captcha = {
	
	getCode: function ($target)
	{
		
		function callback (result)
		{
			$target.val (result.data.code);
		}
		
		if (!$target.length)
		{
			return;
		}
		
		Ice.Controller.call (
			'Captcha/getCode',
			{
				"test": "test",
				"location": location.href
			},
			callback, true
		);
	},
	
	regenerateACodes: function ()
	{
		Ice.Controller_Captcha.getCode ($('input.acaptcha'));
	}
	
};

$(document).ready (function () {
	Ice.Controller_Captcha.regenerateACodes ();
});