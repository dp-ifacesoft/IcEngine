<?php

/**
 * Абстрактный контролер контекта
 *
 * @author morph, neon, goorus
 */
class Controller_Content_Abstract extends Controller_Abstract
{
	/**
	 * Форма создания контента
	 */
	public function create($contentId, $categoryId, $referer)
	{
		$user = User::getCurrent ();
		if (!$user->hasRole ('editor')) {
			return $this->replaceAction ('Error', 'accessDenied');
		}
		$content = Model_Manager::get ('Content', $contentId);
		if ($content->key ()) {
			$category = $content->Content_Category;
		} else {
			$category = Model_Manager::byKey ('Content_Category', $categoryId);
		}
		if (!$category) {
			return $this->replaceAction ('Error', 'notFound');
		}
		Registry::set ('category', $category);
		$extending = Model_Manager::get ($category->controller, $contentId);
		Loader::load ('Temp_Content');
		$tc = Temp_Content::create ($this, 'Content');
		$tc->attr (array (
			'referer'			=> $referer,
			'contentId'			=> $contentId,
			'categoryId'		=> $category->key ()
		));
		$this->_output->send (array (
			'tc'			=> $tc,
			'content'		=> $content,
			'category'		=> $category,
			'referer'		=> $referer
		));
		$data = $extending->beforeCreate ($this->_input);
		if ($data) {
			$this->_output->send ($data);
		}
		$this->_task->setTemplate (
			'Controller/' . str_replace ('_', '/', $category->controller) .
			'/create'
		);
	}

	/**
	 * Удаление контента
	 */
	public function delete($contentId, $referer)
	{
		$this->_task->setTemplate (null);
		$user = User::getCurrent ();
		if (!$user->hasRole ('editor')) {
			return;
		}
		$content = Model_Manager::byKey ('Content', $contentId);
		if (!$content) {
			return;
		}
		$extending = $content->extending ();
		if (!$extending) {
			return;
		}
		$category = $content->Content_Category;
		$content->delete ();
		$extending->delete ();
		$redirect = $referer ?: $category->url;
		Helper_Header::redirect ($redirect);
	}

	/**
	 * Удалить изображение
	 */
	public function removeImage($imageId)
	{
		$this->_task->setTemplate (null);
		$user = User::getCurrent ();
		if (!$user->hasRole ('editor')) {
			return;
		}
		$image = Model_Manager::byKey ('Component_Image', $imageId);
		if (!$image) {
			return;
		}
		$image->delete ();
		$this->_output->send (array(
			'data'	=> array (
				'imageId' => $imageId
			)
		));
	}

	/**
	 * Список контента
	 */
	public function roll($categoryId, $url = null)
	{
		if ($categoryId) {
			$category = Model_Manager::byKey ('Content_Category', $categoryId);
		} elseif ($url) {
			$category = Model_Manager::byOptions (
				'Content_Category',
				array (
					'name'	=> '::Url',
					'url'	=> $url
				)
			);
		} else {
			return $this->replaceAction ('Error', 'notFound');
		}
		if (!$category) {
			return $this->replaceAction ('Error', 'notFound');
		}
		$parent = Model_Manager::byKey ('Content_Category', $category->parentId);
		if (!$parent) {
			return $this->replaceAction ('Error', 'notFound');
		}
		$contentCollection = Model_Collection_Manager::create ('Content')
			->addOptions (
				array (
					'name'	=> 'Content_Category',
					'id'	=> $category->key ()
				),
				array (
					'name'	=> 'Extending',
					'model'	=> $category->extedingModel ()
				)
			);
        $user = User::getCurrent ();
		if (!$user->hasRole ('editor')) {
			$contentCollection->addOptions ('::Active');
		}
        if (!$user->hasRole ('editor')) {
            $contentCollection->addOptions ('::Active');
        }
		$this->_output->send (array (
			'contents'		=> $contentCollection,
			'category'		=> $category,
			'canEdit'		=> $user->hasRole ('editor'),
			'parent'		=> $parent,
		));
	}

	/**
	 * Сохранить контент
	 */
	public function save($title, $short, $text, $sort, $url, $utcode)
	{
		$this->_task->setTemplate (null);
		$user = User::getCurrent ();
		if (!$user->hasRole ('editor')) {
			return $this->replaceAction ('Error', 'accessDenied');
		}
		$text = stripslashes ($text);
		Loader::load ('Temp_Content');
		$tc = Temp_Content::byUtcode ($utcode);
		if (!$tc) {
			return $this->replaceAction ('Error', 'notFound');
		}
		$referer = $tc->attr ('referer');
		$contentId = $tc->attr ('contentId');
		$categoryId = $tc->attr ('categoryId');
		$category = Model_Manager::byKey ('Content_Category', $categoryId);
		if (!$category) {
			return $this->replaceAction ('Error', 'notFound');
		}
		$content = Model_Manager::get ('Content', $contentId);
		$content->update (array (
			'title'					=> $title,
			'short'					=> $short,
			'content'				=> $text,
			'sort'					=> $sort,
			'Content_Category__id'	=> $category->key (),
			'name'					=> $category->extendingModel (),
			'url'					=> $url,
			'extending'				=> $category->controller
		));
		$extending = Model_Manager::get($category->controller, $content->key());
		$fields = array ();
		if (!$content->url) {
			$fields['url'] = $extending->defaultUrl ();
		}
		if (!$content->createdAt) {
			$fields['createdAt'] = Helper_Date::toUnix ();
		}
		if ($fields) {
			$content->update ($fields);
		}
		$tc->component ('Image')->rejoin ($content);
		$extending->afterSave ($this->_input);
        $this->_output->send(array(
            'data'  => array(
                'redirectUrl'   => $referer
            )
        ));
		Helper_Header::redirect ($referer);
	}

	/**
	 * Вывести контект
	 */
	public function view($contentId, $url)
	{
		if ($contentId) {
			$content = Model_Manager::byKey ('Content', $contentId);
		} elseif ($url) {
			$content = Model_Manager::byOptions (
				'Content',
				array (
					'name'	=> '::Url',
					'url'	=> $url
				)
			);
		} else {
			return $this->replaceAction ('Error', 'notFound');
		}
		if (!$content) {
			return $this->replaceAction ('Error', 'notFound');
		}
		$category = $content->Content_Category;
		if (!$category) {
			return $this->replaceAction ('Error', 'notFound');
		}
		$user = User::getCurrent ();
		$this->_output->send (array (
			'content'	=> $content,
			'category'	=> $category,
			'canEdit'	=> $user->hasRole ('editor')
		));
	}

	/**
	 * Загрузка изображений
	 */
	public function uploadImage($utcode)
	{
		$user = User::getCurrent ();
	    if ($user->key ()) {
			return $this->replaceAction ('Error', 'accessDenied');
		}
		Loader::multiLoad ('Temp_Content', 'Helper_Image');
	    $tc = Temp_Content::byUtcode ($utcode);
	    $image = Helper_Image::uploadSimple (
		    $tc->modelName (), $tc->key (), 'Content'
	    );
		$this->_output->send ('image', $image);
	}
}