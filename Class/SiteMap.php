<?php

class SiteMap 
{
	
	/**
	 * @return Route_Collection
	 */
	public static function asList ()
	{
		Loader::load ('Route_Collection');
		$routes = new Route_Collection ();
		$routes->
			where ('visible=1')->
			where ('viewRenderId=1');
			
		$routes->items ();
			
		return $routes->sortByParent ();
	}
	
}