<?php
/**
 *
 * @desc Контролер категорий контента.
 * Примечание: в адресе (поле url) категории не должно быть завершающего слеша.
 * @author ilya
 * @package IcEngine
 *
 */
class Controller_Content_Category_Abstract extends Controller_Abstract
{

	/**
	 * @desc Возвращает название модели категории.
	 * @return string
	 * @override
	 */
	protected function __categoryModel ()
	{
		return 'Content_Category';
	}

	/**
	 * @desc Возвращает название модели контента.
	 * @return string
	 */
	protected function __contentModel ()
	{
		return 'Content';
	}

	/**
	 * @desc Создает и возвращает контроллер
	 */
	public function __construct ()
	{
		Loader::load ('Helper_Header');
		Loader::load ('Helper_Link');
		Loader::load ('Acl_Resource');
	}

	/**
	 * @desc Фабрик метод для получения реферера при удалении
	 * @param Model $category
	 * @param string $referer
	 * @return string
	 * @override
	 */
	protected function __deleteReferer (Model $content_category, $referer)
	{
		return rtrim ($referer, '/');
	}

	protected function __makeUniqueLink ($link, $category_id)
	{
		$content_category = Model_Collection_Manager::byQuery (
			'Content_Category',
			Query::instance ()
				->where ('url', $link)
		);

		if ($category_id)
		{
			$content_category->where ('id!=?', $category_id);
		}

		if (!$content_category->count())
		{
			$result = $link;
		}
		else
		{
			$unique = (int) $content_category->count();
			$linka = preg_split ('/_([0-9])$/', $link, -1, PREG_SPLIT_DELIM_CAPTURE);
			if ($linka)
			{
				$link = $linka [0];
				$unique = (isset ($linka [1]) ? $linka [1] : 0) + 1;
			}
			$link_tmp = $link . '_' . $unique;
			$result = $this->__makeUniqueLink ($link_tmp);
		}

		return $result;

	}

	/**
	 * @desc Фабрик метод для получения разрешения на редактировине
	 * для списка
	 * @param Model $category
	 * @return boolean
	 * @override
	 */
	protected function __rollAcl (Model $category)
	{
		return User::getCurrent ()->isAdmin ();
	}

	/**
	 * @desc Вызывается после roll.
	 */
	protected function __rollAfter ()
	{
	}

	/**
	 * @desc Фабрик метод для получения реферера для списка
	 * @param Model $category
	 * @param string $url
	 * @return string
	 * @override
	 */
	protected function __rollReferer (Model $category, $url = '')
	{
		return $category->url;
	}

	/**
	 * @desc Вызывает после создания категории
	 * @param array $params
	 * @override
	 */
	protected function __saveAfter ($params)
	{

	}

	/**
	 * @desc Фабрик метод для создания css-класса контролера
	 * @param array $params
	 * @return string
	 * @override
	 */
	protected function __saveClass ($params)
	{
		Loader::load ('Helper_Translit');
		$title = $params ['title'];
		return Helper_Translit::makeUrlLink ($title, 'en');
	}

	/**
	 * @desc Фабрик метод для получения реферера при сохранении
	 * @param array $params
	 * @param Model $category
	 * @param string $url
	 * @return string
	 * @override
	 */
	protected function __saveReferer (array $params, Model $content_category, $url)
	{
		$referer = isset ($params ['referer'])
		? $params ['referer']
		: '';
		return ($content_category->url != $url) ? $url : $referer;
	}

	/**
	 * @desc Факторик метод для создания URL контролера
	 * @param array $params
	 * @return string
	 * @override
	 */
	protected function __saveUrl ($params, $category_id)
	{
		$parent = $params ['parent'];
		Loader::load ('Helper_String');
		$url =
			Helper_String::end ($parent->url, '.html') ?
				substr ($parent->url, 0, -5) :
				$parent->url;
		$result = rtrim ($url, '/') . '/' . $this->__saveClass ($params);
		$link = $this->__makeUniqueLink($result, $category_id);
		return $link;
	}

	/**
	 * @desc Удаление категории. Зависимые объекты удалит Garbage Collector.
	 * Предназначен для вызова через ajax.
	 * @author Yury Shvedov
	 * @param integer $content_category_id - id категории
	 * @param string $referer - URL, по которому будет направлен
	 * посетитель
	 */
	public function remove ()
	{
		list (
			$id,
			$referer
		) = $this->_input->receive (
			'id',
			'referer'
		);

		$category = Model_Manager::byKey ($this->__categoryModel (), $id);

		if (!$category)
		{
			return $this->replaceAction ('Error', 'notFound');
		}

		$user = User::getCurrent ();

		$resource_delete = Acl_Resource::byNameCheck (array (
			$this->__categoryModel (),
			$id,
			'delete'
		));

		if (!$resource_delete || !$resource_delete->userCan ($user))
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$category->delete ();

		$redirect = $this->_removeRedirect ($category, $referer);

		$this->_task->setTemplate (null);
		$this->_output->send (array (
			'data'		=> array (
				'redirect'	=> $redirect
			)
		));
	}

	/**
	 * @desc Получить список дочерних категорий
	 * @param integer $category_id - id категории
	 * @param string url - url категории
	 * @return Model_Collection
	 * @return string
	 * @return Model
	 * @return boolean
	 */
	public function roll ()
	{
		list (
			$parent_category_id,
			$url
		) = $this->_input->receive (
			'parent_category_id',
			'url'
		);

		if ($parent_category_id)
		{
			$parent_category = Model_Manager::byKey (
				$this->__categoryModel (),
				$parent_category_id
			);
		}
		else
		{
			$parent_category = Model_Manager::byQuery (
				$this->__categoryModel (),
				Query::instance ()
					->where (
						'url',
						rtrim ($url ? $url : Request::uri (), '/')
					)
			);
		}

		if (!$parent_category)
		{
			return $this->replaceAction ('Error', 'notFound');
		}

		$category_collection = $parent_category->childs ();

		if ($category_collection->count ())
		{
			foreach ($category_collection as $category)
			{
				$category->oneContent ();
			}
		}

		$content_collection = Helper_Link::linkedItems (
			$parent_category,
			$this->__contentModel ()
		);

		$this->_output->send (array (
			'categories'		=>	$category_collection,
			'contents'			=>	$content_collection,
			'referer'			=>	$this->__rollReferer ($parent_category, $url),
			'current'			=>	$parent_category,
			'parent'			=>	$parent_category->getParent (),
			'canEdit'			=>	$this->__rollAcl ($parent_category)
		));

		$this->__rollAfter ();
	}

	/**
	 * @desc Сохранить изменения или создать категорию контента
	 * @param integer $parent_id - родительская категория, куда будет
	 * добавлена создаваемая категория
	 * @param integer $content_category_id - id редактируемой категорию.
	 * Если категория создается, то здесь будет null
	 * @param string $title - заголовок категории
	 * @param string $url - URL категории
	 * @param string $class - произвольный класс для категории
	 * @param string $sort - порядок сортировки для категории
	 * @param boolean $active - будет ли показываться категория на сайте
	 * @param string $referer - ссылка, куда будет перенаправлен пользователь
	 */
	public function save ()
	{
		list (
			$parent_category_id,
			$category_id,
			$title,
			$class,
			$url,
			$sort,
			$active,
			$referer
		) = $this->_input->receive (
			'parent_category_id',
			'category_id',
			'title',
			'class',
			'url',
			'sort',
			'active',
			'referer'
		);

		// Получаем родительскую категорию
		$parent = Model_Manager::byKey (
			$this->__categoryModel (),
			$parent_category_id
		);

		if (!$parent)
		{
			return $this->replaceAction ('Error', 'notFound');
		}

		if ($parent->controller) {
			Loader::load($parent->controller . '_Category');
			return $this->replaceAction(
				$parent->controller . '_Category', 'save'
			);
		}

		// Параметры для передачи в фабрик методы
		$params = array (
			'parent'				=> $parent,
			'parent_id'				=> $parent_category_id,
			'content_category_id'	=> $category_id,
			'title'					=> $title,
			'sort'					=> $sort,
			'active'				=> $active,
		);

		// Получаем класс
		$class = !$class ? $this->__saveClass ($params) : $class;

		// Получаем URL
		$url = !$url ? $this->__saveUrl ($params, $category_id) : $url;

		$user = User::getCurrent ();

		$old_url = null;
		if ($category_id)
		{
			$content_category = Model_Manager::byKey (
				$this->__categoryModel (),
				$category_id
			);

			if (!$content_category)
			{
				return $this->replaceAction ('Error', 'notFound');
			}

			$resource_edit = Acl_Resource::byNameCheck (
				$this->__categoryModel (),
				$content_category->key (),
				'edit'
			);

			if (!(($resource_edit && $resource_edit->userCan ($user)) || $user->hasRole ('editor')))
			{
				return $this->replaceAction ('Error', 'accessDenied');
			}

			$old_url = $content_category->url;

			$content_category->update (array (
				'title'						=> $title,
				'url'						=> $url,
				'class'						=> $class,
				'sort'						=> $sort,
				'active'					=> (int) !empty ($active),
				'parentId'					=> $parent->key (),
				'controller'				=> $parent->controller
			));

			$this->__saveAfter (array (
				$referer,
				$resource_edit,
				null,
				null,
				$content_category
			));
		}
		else
		{
			Loader::load ('Content_Category');
			Loader::load ('Acl_Role_Type_Personal');

			$resource_addContent = Acl_Resource::byNameCheck (
				$this->__categoryModel (),
				$parent->key (),
				'addContent'
			);

			$personal_role = $user->role (Acl_Role_Type_Personal::ID, true);

			if (
				!$resource_addContent->userCan ($user) ||
				!$personal_role ||
				!User::id ()
			)
			{
				return $this->replaceAction ('Error', 'accessDenied');
			}

			$category_class = $this->__categoryModel ();

			$content_category = new $category_class (array (
				'title'			=> $title,
				'name'			=> $this->__categoryModel (),
				'url'			=> $url,
				'class'			=> $class,
				'sort'			=> $sort,
				'active'		=> (int) !empty ($active),
				'parentId'		=> $parent->key (),
				'controller'	=> $parent->controller
			));

			$content_category->save ();

			list (
				$resource_edit,
				$resource_delete,
				$resource_addContent
			) = Acl_Resource::create (
				array (
					$this->__categoryModel (),
					$content_category->key ()
				),
				array (
					'edit',
					'delete',
					'addContent'
				)
			);

			$personal_role->attachResource (
				$resource_edit,
				$resource_delete,
				$resource_addContent
			);

			$this->__saveAfter (array (
				$referer,
				$resource_edit,
				$resource_delete,
				$resource_addContent,
				$content_category
			));
		}

		$redirect =
			$old_url == $referer ?
			$url :
			$referer;

		if (!Request::isJsHttpRequest ())
		{
			Helper_Header::redirect ($redirect);
			die ();
		}

		$this->_task->setTemplate (null);
		$this->_output->send (array (
			'redirect'	=> $redirect,
			'data'		=> array (
				'redirect'	=> $redirect
			)
		));
	}

	/**
	 * @desc Ссылка редиректа при удалении.
	 * @param Content_Category $category
	 * @param string $referer
	 * @return string
	 */
	protected function _removeRedirect (Content_Category $category, $referer)
	{
		return $referer;
	}

	/**
	 * @desc Удаление категории. Зависимые объекты удалит Garbage Collector
	 * @param integer $content_category_id - id категории
	 * @param string $referer - URL, по которому будет направлен
	 * посетитель
	 */
	public function delete ()
	{
		list (
			$category_id,
			$referer
		) = $this->_input->receive (
			'category_id',
			'referer'
		);

		$category = Model_Manager::byKey (
			$this->__categoryModel (),
			$category_id
		);

		if (!$category)
		{
			return $this->replaceAction ('Error', 'notFound');
		}

		$user = User::getCurrent ();

		$resource_delete = Acl_Resource::byNameCheck (array (
			$this->__categoryModel (),
			$category_id,
			'delete'
		));

		if (!$resource_delete || !$resource_delete->userCan ($user))
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$category->delete ();

		//$referer = $this->__deleteReferer ($category, $referer);

		if (Request::isPost ())
		{
			$this->_task->setTemplate (null);
			$this->_output->send (array (
				'redirect'	=> $referer,
				'data'		=> array (
					'redirect'	=> $referer
				)
			));
		}
		else
		{
			// GET запрос
			Helper_Header::redirect ($referer);
		}
	}

}
