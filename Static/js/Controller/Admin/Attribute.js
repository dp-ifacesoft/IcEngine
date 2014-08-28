var Controller_Admin_Attribute = {

	viewFormAdd : function () {
		$('#add_attr').show ();
		$('#attrs #view').hide ();
	},

	add : function (form) {
		var data = Helper_Form.asArray ($(form));

		function callback (result) {

			function callbackIndex (result)
			{
				$('#attrs').html (result.html);
			}

			Controller.call (
				'Admin_Attribute/index',
				{
					table : data.table,
					rowId : data.rowId
				},
				callbackIndex
			);
		}

		$('#add_attr').hide ();

		Controller.call (
			'Admin_Attribute/add',
			data,
			callback
		);
	}
};


