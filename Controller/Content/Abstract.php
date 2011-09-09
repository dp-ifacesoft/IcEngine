<?php
/**
 * @desc Контролер контекта 
 * @author ilya
 * @package IcEngine
 */
class Controller_Content_Abstract extends Controller_Abstract
{
	
	/**
	 * @desc Создает и возвращает контроллер. 
	 * Загружает используемые классы.
	 */
	public function __construct ()
	{
		Loader::load ('Helper_Header');
		Loader::load ('Content');
		Loader::load ('Temp_Content');
		Loader::load ('Content_Collection');
	}
	
	/**
	 * @desc После успешного начала создания.
	 * @override
	 */
	protected function _afterCreate ()
	{
		
	}
	
	/**
	 * @desc После успешного сохранения контента
	 * @param Content $content Сохраняемый контент.
	 * $content->data ('tc') Содержит ссылку на временный контент.
	 * @param boolean $is_new true, если это новый контент, иначе false.
	 * @override
	 */
	protected function _afterSave (Content $content, $is_new)
	{
	}
	
	/**
	 * @desc Название модели расширения
	 * @override
	 */
	protected function _extendingModel ()
	{
		return ''; // без расширения
	}
	
	/**
	 * @desc 
	 * @return Content_Category
	 */
	protected function _getInputCategory ()
	{
		list (
			$category_id,
			$url
		) = $this->_input->receive (
			'category_id',
			'url'
		);
		
		if ($category_id)
		{
			return Model_Manager::byKey (
				$this->__categoryModel (),
				$category_id
			);	
		}
		
		return Model_Manager::byQuery (
			$this->__categoryModel (),
			Query::instance ()
				->where ('url', $url ? $url : Request::uri ())
		);		
	}
	
	/**
	 * @desc
	 * @return Content
	 */
	protected function _getInputContent ()
	{
		list (
			$content_id,
			$url
		) = $this->_input->receive (
			'content_id',
			'url'
		);
		
		if ($content_id)
		{
			return Model_Manager::byKey (
				$this->__contentModel (), 
				$content_id
			);
		}
		
		return Model_Manager::byQuery (
			$this->__contentModel (), 
			Query::instance ()
				->where ('url', $url ? $url : Request::uri ())
		);
	}
	
	/**
	 * @desc Фабрик метод для полечение реферер при сохранении
	 * @param string $url
	 * @param string $referer
	 * @param Content_Abstract $content
	 * @return string
	 */
	protected function _saveReferer ($url, $referer, $content)
	{
		return ($url != $content->url) ? $url : $referer;
	}
	
	/**
	 * @desc Получить имя контейнера
	 * @return string
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
	 * @desc Имеет ли текущий пользователь права на
	 * добавление в категорию
	 * @param Model $content_category
	 * @return boolean
	 */
	public function __checkAcl ($content_category)
	{
		$user = User::getCurrent ();
		
		if ($user->isAdmin ())
		{
			return true;
		}

		if ($user->id ())
		{
			Loader::load ('Acl_Resource');

			$resource_addContent = Acl_Resource::byNameCheck (
				$this->__categoryModel (),
				$content_category->key (),
				'addContent'
			);

			return (bool) ($resource_addContent 
				&& $resource_addContent->userCan ($user));
		}

		return false;
	}
	
	/**
	 * @desc Фабрик метод для полечение рефера при создании
	 * @param Model $content
	 * @param Model $content_category
	 * @param string $referer
	 * @return string
	 */
	protected function __createReferer (Model $content, 
		Model $contetn_category, $referer)
	{
		return $referer;
	}
	
	/**
	 * @desc Фабрик метод для полечение URL при создании
	 * @param Model $content
	 * @param Model $content_category
	 * @param string $url
	 * @return string
	 */
	protected function __createUrl (Model $content, 
		Model $contetn_category, $url)
	{
		return $url;
	}
	
	/**
	 * @desc Фабрик метод для полечение URL при удалении
	 * @param Model $content
	 * @param string $url
	 * @return string
	 */
	protected function __deleteUrl (Model $content, $url)
	{
		return $url;
	}
	
	/**
	 * @desc Фабрик метод для полечение рефера для списка
	 * @param Model $content_category
	 * @return string
	 */
	protected function __rollReferer (Model $content_category)
	{
		return Request::uri ();
	}
	
	/**
	 * @desc Фабрик метод для полечение URL при сохранении
	 * @param string $url
	 * @param string $referer
	 * @param string $title
	 * @return string
	 */
	protected function __saveUrl ($url, $referer, $title)
	{
		Loader::load ('Helper_Translit');
		return rtrim ($url, '/') . '/' . 
			Helper_Translit::makeUrlLink ($title) . '.html';
	}
	
	/**
	 * @desc Список статей
	 * @param integer $content_category_id - id контейнера
	 * @param string $url - URL контейнера
	 * @return Model_Collection $contents
	 * @return Model $category
	 * @return boolean canEdit
	 * @return Model $parent
	 * @return string $referer
	 * @return string $url
	 */
	public function roll ()
	{
		$category = $this->_getInputCategory ();
		
		if (!$category)
		{
			return $this->replaceAction ('Error', 'notFound');
		}

		$parent = Model_Manager::byKey (
			$this->__categoryModel (),
			$category->parentKey ()
		);
		
		if (!$parent)
		{
			return $this->replaceAction ('Error', 'notFound');
		}
		
		$content_collection = Helper_Link::linkedItems (
			$category,
			$this->__contentModel ()
		);

		$parent_url = rtrim ($parent->url, '/') . '.html';

		$agency_link = $this->_input->receive('agency_link');
		$agency = null;
		if ($agency_link)
		{
			$agency = Model_Manager::byQuery(
				'Agency',
				Query::instance()
					->where('linka', $agency_link.'.html')
					->where('city', City::id ())
			);
		}

		$this->_output->send (array (
			'contents'		=> $content_collection,
			'category'		=> $category,
			'canEdit'		=> $this->__checkAcl ($category),
			'parent'		=> $parent,
			'parent_url'	=> $parent_url,
			'referer'		=> $this->__rollReferer ($category),
			'agency'		=> $agency
		));
	}
	
	/**
	 * @desc Вывести контект
	 * @param integer $content_id - id контента
	 * @param string $url - URL контента
	 * @return Model $content,
	 * @return Model $content_category
	 * @return boolean $canEdit
	 */
	public function view ()
	{
		$content = $this->_getInputContent ();
			
		if (!$content)
		{
			return $this->replaceAction ('Error', 'notFound');
		}
		
		$content_category = Model_Manager::byKey (
			$this->__categoryModel (),
			$content->Content_Category__id
		);
		
		if (!$content_category)
		{
			return $this->replaceAction ('Error', 'notFound');
		}

		$agency_link = $this->_input->receive('agency_link');
		$agency = null;
		if ($agency_link)
		{
			$agency = Model_Manager::byQuery(
				'Agency',
				Query::instance()
					->where('linka', $agency_link.'.html')
					->where('city', City::id ())
			);
		}

		$this->_output->send (array (
			'content'	=> $content,
			'category'	=> $content_category,
			'url'		=> $content_category->url,
			'referer'	=> $this->__rollReferer ($content_category),
			'canEdit'	=> $this->__checkAcl ($content_category),
			'agency'	=> $agency
		));
	}
	
	/**
	 * @desc Создать или редактировать инстанс статьи
	 * @param integer $category_id
	 * @param integer $content_id
	 * @param string $referer
	 * @param string $url,
	 * @param string $back
	 * @return Temp_Content $tc
	 * @return Model $content
	 * @return Model $content_category
	 * @return string $url
	 * @return string $back
	 * @return string $referer
	 */
	public function create ()
	{
		list (
			$category_id,
			$content_id,
			$referer,
			$url,
			$back
		) = $this->_input->receive (
			'category_id',
			'content_id',
			'referer',
			'url',
			'back'
		);

		if (!$category_id)
		{
			if (!$content_id)
			{
				return $this->replaceAction ('Error', 'notFound');
			}
			else
			{
				$content = Model_Manager::get (
					$this->__contentModel (),
					$content_id
				);

				$category_id = $content->Content_Category->id;
			}
		}
		
		$category = Model_Manager::byKey (
			$this->__categoryModel (),
			$category_id
		);
			
		if (!$category)
		{
			return $this->replaceAction ('Error', 'notFound');
		}
		
		if ($category->controller && $category->controller != $this->name ())
		{
			return $this->replaceAction (
				$category->controller,
				'create'
			);
		}

		$user = User::getCurrent ();

		if (!User::authorized())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		Loader::load ('Acl_Resource');

		$resource_addContent = Acl_Resource::byNameCheck (
			$this->__categoryModel (),
			$category_id,
			'addContent'
		);
		
		if (
			!User::getCurrent ()->isAdmin() &&
			(
				!$resource_addContent || 
				!$resource_addContent->userCan (User::getCurrent ())
			)
		)
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		if (!isset ($content) || !$content)
		{
			$content = Model_Manager::get (
				$this->__contentModel (),
				$content_id
			);
		}
		
		$tc = Temp_Content::create ($this);
		$tc->attr (array (
			'controller'	=> $this->name (),
			'back'			=> $back,
			'referer'		=> $this->__createReferer ($content, $category, $referer),
			'content_id'	=> $content_id,
			'category_id'	=> $category_id
		));
		
		$this->_output->send (array (
			'tc' 				=> $tc,
			'content'			=> $content,
			'category'			=> $category,
			'url'				=> $this->__createUrl ($content, $category, $url),
			'back'				=> $back,
			'referer'			=> $this->__createReferer ($content, $category, $referer)
		));
		
		$this->_afterCreate ();
	}
	
	/**
	 * @desc Создать/сохранить контент
	 * @param string $title
	 * @param string $short
	 * @param string $text
	 * @param string $utcode
	 * @param integer $content_id
	 * @param integer $content_category_id
	 * @param string $url
	 */
	public function save ()
	{
		list (
			$title, 
			$short,
			$text,
			$utcode,
			$url
		) = $this->_input->receive (
			'title', 
			'short',
			'text',
			'utcode',
			'url'
		);
		
		// Убираем слешы
		$text = stripslashes ($text);

		if (!$utcode)
		{
			return $this->replaceAction ('Error', 'obsolete');
		}
		
		$tc = Temp_Content::byUtcode ($utcode);
		
		if ($tc->attr ('controller') != $this->name ())
		{
			return $this->replaceAction (
				$tc->attr ('controller'),
				'save'
			);
		}
		
		$category_id = $tc->attr ('category_id');
		$content_category = Model_Manager::byKey (
			$this->__categoryModel (),
			$category_id
		);
		
		$user = User::getCurrent ();
		
		$resource_addContent = Acl_Resource::byNameCheck (
			$this->__categoryModel (),
			$category_id,
			'addContent'
		);
		
		if (
			!User::getCurrent ()->isAdmin () &&
			(
				!$resource_addContent || 
				!$resource_addContent->userCan ($user)
			)
		)
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		$back = $tc->attr ('back');
		$referer = $tc->attr ('referer');
		$content_id = $tc->attr ('content_id');
		$url = $this->__saveUrl (
			$content_category->url,
			$referer, $title
		);
		
		if ($content_id)
		{			
			$content = Model_Manager::byKey (
				$this->__contentModel (),
				$content_id
			);

			$referer = $this->_saveReferer ($url, $referer, $content);

			$content->update (array (
				'title'			=> $title,
				'short'			=> $short,
				'content'		=> $text,
				'url'			=> $url
			));
		}
		else
		{
			$content = new Content (array (
				'title'					=> $title,
				'short'					=> $short,
				'content'				=> $text,
				'createdAt'				=> Helper_Date::toUnix (),
				'url'					=> $url,
				'Content_Category__id'	=> $category_id,
				'extending'				=> $this->_extendingModel ()
			));
			
			$content->save ();
			
			// Если это контент с расширением, создаем расширение
			$content->extending ();
			
			Loader::load ('Helper_Link');
			
			if (!$content_category)
			{
				return $this->replaceAction ('Error', 'notFound');
			}
			
			Helper_Link::link (
				$content,
				$content_category
			);

			if ($back)
			{
				$referer = $url;
			}
		}

		$tc->component ('Image')->rejoin ($content);
		
		$is_new = !$content_id;
		
		$content->data ('tc', $tc);
		$this->_afterSave ($content, $is_new);
		
		if (!Request::isJsHttpRequest ())
		{
			Helper_Header::redirect ($referer);
			die ();
		}
		
		$this->_task->setTemplate (null);
		$this->_output->send (array (
			'redirect'	=> $referer,
			'data'		=> array (
				'redirect'	=> $referer
			)
		));
	}
	
	/**
	 * @desc Удалить контент
	 * @param integer $content_id
	 * @param string $url
	 */
	public function delete () 
	{
		list (
			$content_id,
			$url
		) = $this->_input->receive (
			'content_id', 
			'url'
		);
		
		$content = Model_Manager::byKey (
			$this->__contentModel (), 
			$content_id
		);
		
		if (!$content)
		{
			return $this->replaceAction ('Error', 'notFound');
		}
		
		Loader::load ('Helper_Link');
		
		$category_collection = Helper_Link::linkedItems (
			$content,
			'Content_Category'
		);
		
		if (!$category_collection->count ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		$category = $category_collection->first ();
		
		$user = User::getCurrent ();

		$resource_addContent = Acl_Resource::byNameCheck (
			$this->__categoryModel (),
			$category->key (),
			'addContent'
		);
		  
		if (
			!User::getCurrent ()->isAdmin () &&
			(
				!$resource_addContent || 
				!$resource_addContent->userCan ($user)
			)
		)
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$content->delete ();

		$url = $this->__deleteUrl ($content, $url);
		
		$this->_task->setTemplate (null);
		
		if (Request::isPost ())
		{
			$this->_output->send (array (
				'redirect'	=> $url,
				'data'		=> array (
					'redirect'	=> $url
				)
			));
		}
		else
		{
			Helper_Header::redirect ($url);
		}
	}

	public function uploadImage ()
	{
	    if (!User::authorized ())
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		Loader::load ('Temp_Content');
	    $utcode = $this->_input->receive('utcode');
	    $tc = Temp_Content::byUtcode ($utcode);
		
	    Loader::load ('Helper_Image');
	    $image = Helper_Image::uploadSimple (
		    $tc->modelName(),
		    $tc->key (),
		    'content_image'
	    );

		$this->_output->send ('image', $image);
	}
	
	public function remove ()
	{
		$id = $this->_input->receive ('id');
		
		$content = Model_Manager::byKey ($this->__contentModel (), $id);
		
		if (!$content)
		{
			return $this->replaceAction ('Error', 'notFound');
		}
		
		$category = $content->Content_Category;
		
		if (!$category)
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
		
		$user = User::getCurrent ();

		$resource_addContent = Acl_Resource::byNameCheck (
			$this->__categoryModel (),
			$category->key (),
			'addContent'
		);
		  
		if (
			!User::getCurrent ()->isAdmin () &&
			(
				!$resource_addContent || 
				!$resource_addContent->userCan ($user)
			)
		)
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}

		$content->delete ();
	}
	
	public function removeImage ()
	{
		$image_id = (int) $this->_input->receive ('image_id');
		$image = Model_Manager::byQuery (
			'Component_Image',
			Query::instance ()
				->where ('id', $image_id)
				->where ('User__id', User::id ())
		);

		if (!$image)
		{
			$this->_sendError (
				'not_found',
				__METHOD__,
				'/not_found'
			);
			return;
		}

		$image->delete ();
		
		$this->_output->send (
			'data', array ('image_id' => $image_id)
		);
	}

	public function check ()
	{
		list (
			$title, 
			$content_category_id
		) = $this->_input->receive (
			'title',
			'content_category_id'
		);

		$content = Model_Manager::byQuery (
			'Content',
			Query::instance ()
				->where ('Content_Category__id', $content_category_id)
				->where ('title', $title)
		);

		$this->_output->send(
			'content', $content
		);
	}
} 
