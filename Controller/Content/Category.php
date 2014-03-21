<?php

/**
 * Контроллер категорий контента
 *
 * @author morph, goorus
 */
class Controller_Content_Category extends Controller_Abstrtact
{
	/**
	 * Удалить категорию
	 */
	public function remove($categoryId)
	{
		$this->_task->setTemplate(null);
		$category = Model_Manager::byKey('Content_Category', $categoryId);
		if (!$category) {
			return;
		}
		$user = User::getCurrent();
		if (!$user->hasRole('editor') && $category->User__id != $user->key()) {
			return;
		}
		$category->delete();
	}

	/**
	 * Вывести список категорий
	 */
	public function roll($parentId)
	{
		$parent = Model_Manager::byKey('Content_Category', $parentId);
		if (!$parent) {
			return $this->replaceAction('Error', 'notFound');
		}
		$categoryCollection = Model_Collection_Manager::create(
			'Contnet_Category'
		)->addOptions(
			'::Active',
			array(
				'name'	=> '::Parent',
				'id'	=> $parentId
			)
		);
		$this->_output->send(array(
			'parent'		=> $parent,
			'categories'	=> $categoryCollection
		));
	}

	/**
	 * Сохранение категории
	 */
	public function save($parentId, $categoryId, $title, $sort, $active,
		$url, $class)
	{
		$this->_task->setTemplate(null);
		$parent = Model_Manager::byKey('Content_Category', $parentId);
		if (!$parent) {
			return;
		}
		$user = User::getCurrent();
		if (!$user->hasRole('editor') || $user->key() != $parent->User__id) {
			return;
		}
		$category = Model_Manager::get('Content_Category', $categoryId);
		$category->set(array(
			'parentId'	=> $parent->key(),
			'title'		=> $title,
			'sort'		=> $sort,
			'User__id'	=> $user->key(),
			'active'	=> $active,
			'url'		=> $url,
			'class'		=> $class,
			'name'			=> $parent->name,
			'controller'	=> $parent->controller
		));
		$category->save();
	}
}