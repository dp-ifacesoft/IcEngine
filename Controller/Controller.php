<?php

/**
 * Контроллер контроллеров.
 *
 * @author Юрий, neon
 */
class Controller_Controller extends Controller_Abstract
{
	/**
	 * Ajax вызов контроллера
     *
     * @Route(
     *      "/Controller/ajax/",
     *      "name"="ajaxPage",
     *      "weight"=10,
     *      "params"={
     *          "View_Render__id"=3
     *      }
     * )
	 */
	public function ajax($call, $back, $params)
	{
        $controllerManager = $this->getService('controllerManager');
        $_SERVER ['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		if (is_string($params)) {
			$params = json_decode(
				urldecode($params),
				true
			);
		}
		$result = $controllerManager->html(
			urldecode($call),
			$params ? $params : array(),
			false
		);
		$this->output->send(array(
			'back'		=> $back,
			'result'	=> $result
		));
	}

	/**
	 * Вызов экшена контроллера по названию из входных параметров
	 */
	public function auto($controller, $action)
	{
		return $this->replaceAction($controller, $action);
	}

	public function create ($name, $action, $author, $comment)
	{
		$helperCodeGenerator = $this->getService('helperCodeGenerator');
        $helperDate = $this->getService('helperDate');
        $filename = IcEngine::root() . 'Ice/Controller/' .
			str_replace('_', '/', $name) . '.php';
		if (file_exists($filename)) {
			return;
		}
		$dir = dirname($filename);
		if (!is_dir($dir)) {
			mkdir($dir, 0750, true);
		}
		$action = explode(',', $action);
		foreach ($action as &$a) {
			$a = trim ($a);
		}
		$output = $helperCodeGenerator->fromTemplate(
			'controller',
			array(
				'name'		=> $name,
				'actions'	=> $action,
				'comment'	=> $comment,
				'author'	=> $author,
				'package'	=> 'Vipgeo',
				'date'		=> $helperDate->toUnix()
			)
		);
		echo 'File: ' . $filename . PHP_EOL;
		file_put_contents($filename, $output);
		$dir = IcEngine::root() . 'Ice/View/Controller/' .
			str_replace('_', '/', $name) . '/';
		if (!is_dir($dir)) {
			mkdir($dir, 0750, true);
		}
		foreach ($action as $a) {
			$filename = $dir . $a . '.tpl';
			if (file_exists($filename)) {
				continue;
			}
			echo 'View: ' . $filename . PHP_EOL;
			file_put_contents($filename, '');
		}
	}

    /**
	 * Ajax вызов контроллера (синхронный)
     *
     * @Route(
     *      "/Controller/sync/",
     *      "name"="syncPage",
     *      "weight"=10,
     *      "params"={
     *          "View_Render__id"=5
     *      }
     * )
	 */
	public function sync($call, $back, $params)
	{
        $controllerManager = $this->getService('controllerManager');
		if (is_string($params)) {
			$params = json_decode(
				urldecode($params),
				true
			);
		}
		$result = $controllerManager->html(
			urldecode($call),
			$params ? $params : array(),
			false
		);
		$this->output->send(array(
			'back'		=> $back,
			'result'	=> $result
		));
	}
}
