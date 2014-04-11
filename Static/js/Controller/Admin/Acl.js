var Controller_Admin_Acl = {
	saveOneResource : function (resource_name, checked, role_id) {
		Controller.call (
			'Admin_Acl/saveOneResource',
			{
				resource_name : resource_name,
				checked :checked,
				role_id : role_id
			},
			callback,
			false
		);
		function callback (result) {
			
		}
	}
	
}