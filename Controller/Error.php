<?php

class Controller_Error extends Controller_Abstract
{

    public function e403 ()
    {
        Header::setStatus (Header::E403);
    }

	public function e404 ()
	{
		Header::setStatus(Header::E404);
	}

	/**
	 * @desc Доступ запрещен.
	 */
	public function accessDenied ()
	{
		$this->replaceAction ('Authorization', 'accessDenied');
	}

	/**
	 * @desc Пустое вместо
	 */
	public function blank ()
	{
	}

	/**
	 * @desc Страница не найдена
	 */
	public function notFound ()
	{
		$this->_output->send (array (
			'error'	=> 'not found',
			'data'	=> array (
				'error' => 'not found'
			)
		));
	}

	/**
	 * @desc Страница устарела.
	 * В большинстве случаев означает неверный utcode или прикрепление
	 * компонента к несуществующей модели.
	 */
	public function obsolete ()
	{
		$this->_output->send (array (
			'error' => 'page obsolete',
			'data'	=> array (
				'error'	=> 'page obsolete'
			)
		));
	}

}