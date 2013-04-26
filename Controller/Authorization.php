<?php

/**
 * Контроллер авторизации
 *
 * @author morph
 */
class Controller_Authorization extends Controller_Abstract
{
    /**
	 * Выход
     *
     *  @Route(
     *      "/logout/",
     *      "name"="logoutPage",
     *      "weight"=10
     * )
	 */
	public function logout()
	{
        if ($this->getService('request')->isAjax()) {
            $this->task->setTemplate(null);
        }
		$user = $this->getService('user')->getCurrent();
		$user->logout();
		$session = $this->getService('session')->getCurrent();
		$session->delete();
		$request = $this->getService('request');
		$redirect = $this->input->receive('redirect');
		if (!$redirect) {
			$redirect = $request->referer();
		}
		$helperUri = $this->getService('helperUri');
		$redirect = $helperUri->validRedirect(
			$redirect ? $redirect : self::DEFAULT_REDIRECT
		);
		$this->output->send(array(
			'data'  => array(
                'redirect'	=> $redirect
            )
		));
	}
}