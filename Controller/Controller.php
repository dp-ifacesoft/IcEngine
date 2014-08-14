<?php
/**
 *
 * @desc Контроллер контроллеров.
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Controller extends Controller_Abstract
{

	/**
	 * @desc
	 */
	public function ajax ()
	{
		list (
			$call,
			$back,
			$params
		) = $this->_input->receive (
			'call',
			'back',
			'params'
		);

        	$_SERVER ['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

		if (is_string ($params))
		{
			$params = json_decode (
				urldecode ($params),
				true
			);
		}

		$result = Controller_Manager::html (
			urldecode ($call),
			$params ? $params : array (),
			false
		);

		$this->_output->send (array (
			'back'		=> $back,
			'result'	=> $result
		));
	}

	/**
	 * @desc Вызов экшена контроллера по названию из входных параметров
	 */
	public function auto ()
	{
		$controller = $this->_input->receive ('controller');
		$action = $this->_input->receive ('action');

		return $this->replaceAction ($controller, $action);
	}

	public function create ($name, $action, $author, $comment)
	{
		$filename = IcEngine::root () . 'Ice/Controller/' .
			str_replace ('_', '/', $name) . '.php';
		if (file_exists ($filename))
		{
			return;
		}
		$dir = dirname ($filename);
		if (!is_dir ($dir))
		{
			mkdir ($dir, 0750, true);
		}
		$action = explode (',', $action);
		foreach ($action as &$a)
		{
			$a = trim ($a);
		}

		$output = Helper_Code_Generator::fromTemplate (
			'controller',
			array (
				'name'		=> $name,
				'actions'	=> $action,
				'comment'	=> $comment,
				'author'	=> $author,
				'package'	=> 'Vipgeo',
				'date'		=> Helper_Date::toUnix ()
			)
		);
		echo 'File: ' . $filename . PHP_EOL;
		file_put_contents ($filename, $output);
		$dir = IcEngine::root () . 'Ice/View/Controller/' .
			str_replace ('_', '/', $name) . '/';
		if (!is_dir ($dir))
		{
			mkdir ($dir, 0750, true);
		}
		foreach ($action as $a)
		{
			$filename = $dir . $a . '.tpl';
			if (file_exists ($filename))
			{
				continue;
			}
			echo 'View: ' . $filename . PHP_EOL;
			file_put_contents ($filename, '');
		}
	}

	/**
	 *
	 * @param boolean $with_actions
	 * @return array
	 */
	public static function getControllersList ($with_actions = true)
	{
		$controllers = array();
		$postfix = '.php';

		$action_prefix = 'action';
		$action_prefix_len = strlen($action_prefix);

		$ajax_prefix = 'ajax';
		$ajax_prefix_len = strlen($ajax_prefix);

		$post_prefix = 'post';
		$post_prefix_len = strlen($post_prefix);

		$founded_res_ids = array();

		$controller_name2index = array();

		foreach (Loader::$pathes['controller'] as $path)
		{
			$files = scandir($path);
			foreach ($files as $file)
			{
				if (
					substr($file, -strlen($postfix), strlen($postfix)) == $postfix
				)
				{
					$controller = substr(
						$file,
						0,
						- strlen($postfix)
					);

					require_once $path . $file;

					$class = 'Controller_' . $controller;
					$actions = get_class_methods($class);
					if (!empty($actions))
					{
						sort($actions);
					}
					else
					{
						if (class_exists($class))
						{
							trigger_error(
								"No methods found for $class.",
								E_USER_NOTICE
							);
						}
						else
						{
							trigger_error(
								"Class not found $class.",
								E_USER_NOTICE
							);
						}
						$actions = array();
					}
					$resources = array();
					foreach ($actions as $action)
					{
						if (
							substr($action, 0, $action_prefix_len) == $action_prefix ||
							substr($action, 0, $ajax_prefix_len) == $ajax_prefix ||
							substr($action, 0, $post_prefix_len) == $post_prefix
						)
						{
							$res = Acl_Resource::byControllerAction($controller, $action);
							if (!$res)
							{
								$res = new Acl_Resource (false);
								$res->create(array(
									'name'			=> "$controller::$action",
									'controller'	=> $controller,
									'action'		=> $action,
									'type_id'		=> Acl_Resource::TYPE_CONTROLLER_ACTION
								));
							}
							$resources[] = $res;
							$founded_res_ids[] = $res->id;
						}
					}

					$n = count ($controllers);
					$controller_name2index [$controller] = $n;
					$controllers[$n] = array(
						'name'		=> $controller,
						'resources'	=> $resources
					);
				}
			}
		}

		// Несуществующие контроллеры и действия
		$reses = new Acl_Resource_Collection ();
		$reses->where ('id NOT IN (?)', $founded_res_ids);

		foreach ($reses->items () as $resource)
		{
			$controller = $resource->controller;
			if (!isset ($controller_name2index [$controller]))
			{
				$n = count ($controllers);
				$controller_name2index [$controller] = $n;
				$controllers[$n] = array (
					'name'		=> $controller,
					'resources'	=> array (),
					'unexists'	=> true
				);
			}
			else
			{
				$n = $controller_name2index [$controller];
			}

			$controllers [$n]['resources'][] = $resource;
		}


		return $controllers;
	}

	public function multiAction ()
	{
		$actions = $this->_input->receive ('actions');
		$results = array ();

		foreach ($actions as $name => $action)
		{
			$results [$name] = Controller_Manager::html (
				$action ['action'],
				$action
			);
		}

		$this->_output->send (array (
			'data'	=> array (
				'results' => $results
			)
		));

		$this->_task->setTemplate (null);
	}


}
