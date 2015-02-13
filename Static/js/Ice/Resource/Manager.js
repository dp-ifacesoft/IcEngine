/**
 *
 * @desc Мэнеджер ресурсов
 * @author Yury Shvedov, Ilya Kolesnikov
 * @package IcEngine
 * 
 */
Ice.Resource_Manager = {
	
	/**
	 * @var object
	 */
	_resources: {},
	
	/**
	 * @param type string
	 * @param name string
	 * @return mixed
	 */
	get: function (type, name)
	{
		return Ice.Resource_Manager._resources [type]
			? Ice.Resource_Manager._resources [type][name]
			: null;
	},
	
	/**
	 * @param type string
	 * @param name string
	 * @param resource mixed
	 */
	set: function (type, name, resource)
	{
		if (!Ice.Resource_Manager._resources [type])
		{
			Ice.Resource_Manager._resources [type] = {};
		}
		
		Ice.Resource_Manager._resources [type][name] = resource;
	}
	
};