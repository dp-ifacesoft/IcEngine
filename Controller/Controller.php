<?php

/**
 * Контроллер контроллеров.
 *
 * @author goorus, morph, neon
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
     *          "viewRender"="JsHttpRequest"
     *      }
     * )
     * @Validator("Ajax")
	 */
	public function ajax($call, $back, $params)
	{
        $controllerManager = $this->getService('controllerManager');
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		if (is_string($params)) {
			$params = json_decode(urldecode($params), true);
		}
        $params = $params ?: array();
        $call = urldecode($call);
        $annotationManager = $controllerManager->getAnnotationManager();
        $nameParts = explode('/', $call);
        $controller = $nameParts[0];
        $action = !empty($nameParts[1]) ? $nameParts[1] : 'index';
        $annotation = $annotationManager->getAnnotation(
            'Controller_' . $controller
        )->getData()['methods'];
        if (!isset($annotation[$action]) ||
            !isset($annotation[$action]['Ajax'])) {
            $filename = IcEngine::root() . 'log/noajax.log';
            file_put_contents($filename, $call . PHP_EOL, FILE_APPEND);
            //return;
        }
		$result = $controllerManager->html($call, $params, false);
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
		$this->replaceAction($controller, $action);
	}

    /**
	 * Ajax вызов контроллера (синхронный)
     *
     * @Route(
     *      "/Controller/sync/",
     *      "name"="syncPage",
     *      "weight"=10,
     *      "params"={
     *          "viewRender"="Ajax"
     *      }
     * )
     * @Validator("Ajax")
	 */
	public function sync($call, $back, $params)
	{
        $controllerManager = $this->getService('controllerManager');
		if (is_string($params)) {
			$params = json_decode(urldecode($params), true);
		}
		$params = $params ?: array();
        $call = urldecode($call);
        $annotationManager = $controllerManager->getAnnotationManager();
        $nameParts = explode('/', $call);
        $controller = $nameParts[0];
        $action = !empty($nameParts[1]) ? $nameParts[1] : 'index';
        $annotation = $annotationManager->getAnnotation(
            'Controller_' . $controller
        )->getData()['methods'];
        if (!isset($annotation[$action]) ||
            !isset($annotation[$action]['Ajax'])) {
            $filename = IcEngine::root() . 'log/noajax.log';
            file_put_contents($filename, $call . PHP_EOL, FILE_APPEND);
            //return;
        }
		$result = $controllerManager->html($call, $params, false);
		$this->output->send(array(
			'back'		=> $back,
			'result'	=> $result
		));
	}
}