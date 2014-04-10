var Controller_Acl_Role = {
	
	removeResource: function (id)
	{
		function callback (result)
		{
			$('#acl_resource_' + id).remove ();
		}
		
		Controller.call (
			'Acl_Role/removeResource',
			{
				id: id
			},
			callback, false
		);
	}

};