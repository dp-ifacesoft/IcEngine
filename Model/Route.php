<?php

Loader::load ('Model_Child');
/**
 *
 * @desc Роут.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Route extends Model_Child
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Поля роута по умолчанию.
		 * @var array
		 */
		'empty_route'	=> array (
			'route'				=> '',
			'active'			=> 1,
			'View_Render__id'	=> 1,
			'params'			=> array (),
			'weight'			=> 0,
			'title'				=> '',
			'model'				=> '',
			'visible'			=> 0,
			'parentId'			=> 0
		),
		/**
		 * @desc Использовать источник моделей по умолчанию.
		 * @var boolean
		 */
		'use_default_source'	=> true,
		/**
		 * @desc Использовать роутеры из конфига.
		 * @var boolean
		 */
		'use_config_source'		=> true,
		/**
		 * @desc Роутеры.
		 * @var array
		 */
		'routes'	=> array (
			1			=> array (
				'pattern'			=> '^//$',
				'title'				=> 'Главная',
				'visible'			=> 1,
				'parentId'			=> 0,
				'actions'			=> 'Index/index'
			),
			2			=> array (
				'route'				=> '/admin/',
				'pattern'			=> '^/admin/$',
				'title'				=> 'Админка',
				'actions'			=> 'Admin'
			),
			3			=> array (
				'pattern'			=> '^/Controller/ajax',
				'View_Render__id'	=> 3,
				'title'				=> 'ajax запросы контроллера',
				'actions'			=> 'Controller/ajax'
			),
			4			=> array (
				'pattern'			=> '^/Controller/post',
				'View_Render__id'	=> 5,
				'title'				=> 'post запросы контроллера',
				'actions'			=> 'Controller/post'
			),
			5			=> array (
				'pattern'			=> '/',
				'weight'			=> -999999,
				'title'				=> 'Страница не найдена',
				'actions'			=> 'Page/notFound'
			),
			6			=> array (
				'pattern'			=> '^/registration/?$',
				'title'				=> 'Регистрация',
				'visible'			=> 1,
				'parentId'			=> 1,
				'actions'			=> 'Registration'
			),
			7			=> array (
				'route'				=> 'registration/:code',
				'pattern'			=> '^/registration/[^/]{1\,}//*',
				'title'				=> 'Регистрация',
				'parentId'			=> 1,
				'actions'			=> 'Registration/emailConfirm'
			),
			8			=> array (
				'pattern'			=> '^/login/',
				'title'				=> 'Авторизация',
				'parentId'			=> 1,
				'actions'			=> 'Authorization/login'
			)
		)
	);

	/**
	 * @desc Метод для получения ссылки на страницу.
	 * @var string
	 */
	const MODEL_METHOD_GET_LINK			= 'getRouteLink';

	/**
	 * @desc Метод для получения страниц одного уровня с текущей.
	 * @var string
	 */
	const MODEL_METHOD_GET_SIBLINGS		= 'getRouteSiblings';

	/**
	 * @desc Метод для получения названия текущей страницы.
	 * @var string
	 */
	const MODEL_METHOD_GET_TITLE		= 'getRouteTitle';

	/**
	 * @desc Получить роут по урлу
	 * @param string $url
	 * @return Route
	 */
	public static function byUrl ($url)
	{
		$url = '/' . trim ($url, '/') . '/';

		/*
		 * Заменяем /12345678/ на /?/.
		 * Операция применяется дважды, т.к. если в запросе
		 * несколько чисел идет подряд "/content/123/456/789/",
		 * то в результате первого прохода вхождения будут заменены
		 * через раз - "/content/?/456/?/", и только после второго
		 * полностью - "/content/?/?/?/".
		 * Это позволяет привести все запросы с переменными к одному,
		 * который будет закеширован.
		 */
		$pattern = preg_replace ('#/[0-9]{1,}/#i', '/?/', $url);
		$pattern = preg_replace ('#/[0-9]{1,}/#i', '/?/', $pattern);
		$router = Resource_Manager::get ('Route_Cache', $pattern);

		if ($router !== null)
		{
			return $router ? new self ($router) : null;
		}

		$config = Config_Manager::get (
			__CLASS__,
			array (
				'use_default_source'	=> true
			)
		);

		$row = null;

		if ($config ['use_config_source'])
		{
			foreach ($config ['routes'] as $id => $route)
			{
				if (
					preg_match ('#' . $route ['pattern'] . '#', $pattern) &&
					(
						$row == null ||
						(int) $route ['weight'] > (int) $row ['weight']
					)
				)
				{
					$row = array_merge (
						$config ['empty_route']->__toArray (),
						$route->__toArray ()
					);
					$row ['id'] = $id;
				}
			}
		}
		if (!$row)
		{
			Resource_Manager::set ('Route_Cache', $pattern, false);
			return null;
		}

		Resource_Manager::set ('Route_Cache', $pattern, $row);

		return new self ($row);
	}

	/**
	 * @desc Получение ссылки на роут
	 * @return string
	 */
	public function link ()
	{
		if (
			$this->model && Loader::load ($this->model) &&
			method_exists ($this->model, self::MODEL_METHOD_GET_LINK)
		)
		{
			 return call_user_func (
				 array ($this->model, self::MODEL_METHOD_GET_LINK),
				 $this
			 );
		}

		$route = '/';
		$parts = trim ($this->route, '\\/');

		if (!$parts)
		{
			return '/';
		}

		$parts = explode ('/', $parts);

		foreach ($parts as $part)
		{
			$params = explode (':', $part);
			if (count ($params) > 1)
			{
				$route .= Request::param ($params [1]) . '/';
			}
			else
			{
				$route .= $part . '/';
			}
		}

		return $route;
	}

	/**
	 * @desc Формирует ссылку на страницу до части, включающей $stop_key.
	 * Значение для части $stop_key берется из текущего адреса, либо
	 * может быть передано вторым параметром
	 * @param string $stop_key Стоповый параметр.
	 * @param mixed $value [optional] Значение для стопового параметра.
	 * @return string
	 */
	public function linkPart ($stop_key)
	{
		if (func_num_args () > 1)
		{
			$stop_value = func_get_arg (1);
		}
		else
		{
			$stop_value = Request::param ($stop_key);
		}

		$route = trim ($this->route, '\\/');

		if (!$route)
		{
			return '/';
		}

		$link = '/';
		$route = explode ('/', $route);
		foreach ($route as $part)
		{
			$params = explode (':', $part);
			if (count ($params) > 1)
			{
				 if (array_search ($stop_key, $params))
				 {
					 $link .= $stop_value . '/';
					 break;
				 }
				 else
				 {
					 $link .= Request::param ($params [1]) . '/';
				 }
			}
			else
			{
				$link .= $part . '/';
			}
		}

		return $link;
	}

	/**
	 * @desc Сформировать роут экшины, привязаннык роуту.
	 * @return Route_Action_Collection
	 */
	public function actions ()
	{
		Loader::load ('Route_Action');

		if (isset ($this->_fields ['actions']))
		{
			$i = 0;

			$route_action_collection = Model_Collection_Manager::create (
				'Route_Action'
			)
				->reset ();

			$actions =
				is_object ($this->_fields ['actions']) ?
				$this->_fields ['actions']->__toArray () :
				(array) $this->_fields ['actions'];

			foreach ($actions as $action => $assign)
			{
				if (is_numeric ($action))
				{
					if (is_scalar ($assign))
					{
						$action	= $assign;
						$assign = 'content';
					}
					else
					{
						$assign = reset ($assign);
						$action = key ($assign);
					}
				}

				$tmp = explode ('/', $action);

				$controller = $tmp [0];
				$action = !empty ($tmp [1]) ? $tmp [1] : 'index';

				$route_action = new Route_Action (array (
					'Controller_Action'	=> new Controller_Action (array (
						'controller'	=> $controller,
						'action'		=> $action
					)),
					'Route'				=> $this,
					'sort'				=> ++$i,
					'assign'			=> $assign
				));

				$route_action_collection->add ($route_action);
			}
		}
		else
		{
			$route_action_collection = Model_Collection_Manager::byQuery (
				'Route_Action',
				Query::instance ()
					->where ('Route__id', $this->key ())
					->order ('sort')
			);
		}

		return $route_action_collection;
	}

	/**
	 * @desc Заголовок части хлебной крошки.
	 * @return string
	 */
	public function title ()
	{
		if (
			$this->model && Loader::load ($this->model) &&
			method_exists ($this->model, self::MODEL_METHOD_GET_TITLE)
		)
		{
			 return call_user_func (
				 array ($this->model, self::MODEL_METHOD_GET_TITLE),
				 $this
			 );
		}
		return $this->title;
	}

	/**
	 * @desc Получение роутов, находящихся на одном уровне с этим.
	 * @return array
	 */
	public function siblings ()
	{
		if (
			$this->model && Loader::load ($this->model) &&
			method_exists ($this->model, self::MODEL_METHOD_GET_SIBLINGS)
		)
		{
			return call_user_func (
				array ($this->model, self::MODEL_METHOD_GET_SIBLINGS),
				$this
			);
		}

		$siblings = new Route_Collection ();
		$siblings
			->where ('parentId', $this->parentId)
			->where ('id!=?', $this->id)
			->where ('visible=1')
			->where ('active=1');

		$result = array ();

		foreach ($siblings as $sibling)
		{
			$result [] = array (
				'title'	=> $sibling->title,
				'link'	=> $sibling->link ()
			);
		}

		return $result;
	}

	/**
	 * @desc Возвращает объект рендера для роутера.
	 * @return View_Render_Abstract
	 */
	public function viewRender ()
	{
		$render = $this->View_Render;

		if (!$render && isset ($this->_fields ['viewRenderName']))
		{
			$render = View_Render_Manager::byName ($this->_fields ['viewRenderName']);
		}

		return $render;
	}
}