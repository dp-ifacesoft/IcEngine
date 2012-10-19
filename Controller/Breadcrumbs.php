<?php
/**
 *
 * @desc Контроллер для вывода хлебных крошек.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Breadcrumbs extends Controller_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
    public function index ()
    {
        $path = new Route_Collection ();
        $route = Router::getRoute ();
        while ($route)
        {
            $path->add ($route);
            $route = $route->getParent ();
        }

        $this->_output->send (array (
            'path'	=> $path->reverse ()
        ));
    }

}