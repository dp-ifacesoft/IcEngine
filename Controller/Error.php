<?php

/**
 * Контроллер ошибок
 *
 * @author goorus, morph
 */
class Controller_Error extends Controller_Abstract
{
    /**
     * Ошибка 403
     */
    public function e403 ()
    {
        $this->getService('helperHeader')->setStatus(Header::E403);
    }

    /**
     * Ошибка 404
     */
	public function e404 ()
	{
		$this->getService('helperHeader')->setStatus(Header::E404);
	}

	/**
	 * Доступ запрещен.
	 */
	public function accessDenied()
	{
		$this->output->send(array(
			'error'	=> 'access denied',
			'data'	=> array(
				'error' => 'access denied'
			)
		));
		$this->replaceAction('Authorization', 'accessDenied');
	}

	/**
	 * Пустое вместо
	 */
	public function blank()
	{
	}

	/**
	 * Страница не найдена
	 */
	public function notFound ()
	{
        $task = IcEngine::getTask();
        $task->setTemplate('Controller/Front/index');
		$this->output->send(array(
			'error'	=> 'not found',
			'data'	=> array(
				'error' => 'not found'
			)
		));
	}

	/**
	 * Страница устарела
	 * В большинстве случаев означает неверный utcode или прикрепление
	 * компонента к несуществующей модели.
	 */
	public function obsolete()
	{
		$this->output->send(array(
			'error' => 'page obsolete',
			'data'	=> array(
				'error'	=> 'page obsolete'
			)
		));
	}
}