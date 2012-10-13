<?php

Loader::load ('Model_Child');

/**
 *
 * @desc Базовая модель категории контента
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Content_Category extends Model_Child
{

	/**
	 * @desc Возвращает название модели контента.
	 * @return string
	 */
	public function contentModel ()
	{
		return 'Content';
	}

	/**
	 * @desc Возвращает контент, содержащийся в этом разделе.
	 * @return Model_Collection
	 */
	public function contents ()
	{
		return Model_Collection_Manager::byQuery (
			$this->contentModel (),
			Query::instance ()
				->where ('Content_Category__id', $this->key())
		);
	}

	public function contentCount ()
	{
		return DDS::executeAuto (
			Query::instance ()
				->select ('COUNT(*)')
				->from ('Content')
				->where ('Content.Content_Category__id', $this->key ())
		)->getResult ()->asValue ();
	}

	/**
	 * @desc Поменять URL категории если в нем один контент
	 * @return Model
	 */
	public function oneContent ()
	{
		$articles = Helper_Link::linkedItems(
			$this,
			'Content'
		);

		$this->data('content', $articles);

		if ($articles->count () == 1 && !$this->childs()->count ())
		{
			$this->url = $articles->first ()->url;
		}
		return $this;
	}

	public function title ()
	{
		return $this->title . ' ' . $this->url;
	}

}