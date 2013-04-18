<?php
/**
 *
 * @desc Контроллер админки разделов контента
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Admin_Content_Category extends Controller_Abstract
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected $_config = array (
		// Роли, имеющие доступ к админке
		'access_roles'	=> array ('admin')
	);

	/**
	 * @desc Проверяет, есть ли у текущего пользователя доступ
	 * к экшенам этого контроллера
	 * @return boolean true, если пользователь имеет доступ, иначе false.
	 */
	protected function _checkAccess ()
	{
		$user = User::getCurrent ();
		$roles = $this->config ()->access_roles;

		foreach ($roles as $role)
		{
			if ($user->hasRole ($role))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @desc Получение списка дочерних разделов
	 * @param integer $category_id
	 * @param integer $level уровень вложения
	 */
	public function getSubcategories ($category_id, $level)
	{
		$categories = Model_Collection_Manager::create ('Content_Category')
			->addOptions (array (
				'name'	=> 'Parent',
				'id'	=> (int) $category_id
			));

		$contents = Model_Collection_Manager::create ('Content')
			->addOptions (array (
				'name'	=> 'Category',
				'id'	=> (int) $category_id
			));

		$items = array ();

		foreach ($categories as $category)
		{
			$items [] = array (
				'id'			=> $category->key (),
				'is_category'	=> true,
				'title'			=> $category->title,
				'url'			=> $category->url
			);
		}

		foreach ($contents as $content)
		{
			$items [] = array (
				'id'			=> $content->key (),
				'is_category'	=> false,
				'title'			=> $content->title,
				'url'			=> $content->url
			);
		}

		$this->_output->send (array (
			'items'	=> $items,
			'level'	=> ++$level
		));
	}

	/**
	 * @desc Получение списка контента для раздела
	 * @param integer $category_id
	 * @param integer $page
	 */
	public function getSubcontents ($category_id, $page)
	{
		$this->_task->setViewRender (View_Render_Manager::byName ('Xslt'));

		$contents = Model_Collection_Manager::create ('Content')
			->addOptions (array (
				'name'	=> 'Category',
				'id'	=> (int) $category_id
			));

		$paginator = new Paginator ($page);
		$contents->setPaginator ($paginator);

		$this->_output->send (array (
			'contents'	=> $contents,
			'data'		=> array (
				'full_count'	=> $paginator->fullCount
			)
		));
	}

	/**
	 * @desc Дерево разделов контента
	 */
	public function index ()
	{
		if (!$this->_checkAccess ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$categories = Model_Collection_Manager::create ('Content_Category')
			->addOptions ('Root');

		//$categories->setPaginator ($this->getService('paginator')->fromInput ($this->_input));

		$this->_output->send (array (
			'categories'	=> $categories,
			'expand_level'	=> 0
		));
	}

}
