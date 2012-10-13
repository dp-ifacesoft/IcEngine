<?php
/**
 *
 * @desc Абстрактный класс модели контента
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 * @property id
 * @property name
 * @property title
 * @property Content_Category__id
 * @property short
 * @property content
 * @property createdAt
 * @property lastModify
 * @property User__id
 * @property url
 * @property active
 * @property sort
 * @property extending
 *
 */
class Content_Abstract extends Model_Factory_Delegate
{
	/**
	 * @desc Получить базовую модель контента
	 * @return Content
	 */
	public function base ()
	{
		$content = Model_Manager::byKey (
			'Content',
			$this->key ()
		);
		if (!$content)
		{
			return new Content ();
		}
		return new Content ($content->asRow ());
	}

	/**
	 * @desc
	 * @param string $method
	 * @return boolean
	 */
	public function checkAcl ($method)
	{
		$method_name = 'checkAcl' . ucfirst ($method);

		if (!method_exists ($this, $method_name))
		{
			return false;
		}

		return $this->$method_name ();
	}

	/**
	 * @desc
	 * @return boolean
	 */
	public function checkAclCreate ()
	{
		return User::getCurrent ()->hasRole ('admin');
	}

	/**
	 * @desc
	 * @return boolean
	 */
	public function checkAclDelete ()
	{
		return User::getCurrent ()->hasRole ('admin');
	}

	/**
	 * @desc
	 * @return boolean
	 */
	public function checkAclEdit ()
	{
		return User::getCurrent ()->hasRole ('admin');
	}

	/**
	 * @desc
	 * @param mixed $data
	 * @return boolean
	 */
	public function create ($data)
	{
		if (!$this->checkAcl (__METHOD__))
		{
			return false;
		}

		$this->name = $this->delegeeName ();

		$this->updateCarefully ($data);

		return true;
	}

	/**
	 * @desc Получить имя фабрики
	 * @return string
	 */
	public function delegeeName ()
	{
		return substr (get_class ($this), 8);
	}

	/**
	 * @desc редактирование контента
	 * @param mixed $data
	 * @return boolean
	 */
	public function edit ($data)
	{
		if (!$this->checkAcl (__METHOD__))
		{
			return false;
		}

		$this->updateCarefully ($data);

		return true;
	}

	/**
	 * @desc Расширение модели
	 * @return Content_Extending
	 */
	public function extending ()
	{
		if (!$this->extending)
		{
			return null;
		}

		$extending = Model_Manager::byKey ($this->extending, $this->id);

		if (!$extending && $this->extending && $this->id)
		{
			Loader::load ('Content');
			// Расширение не создано
			$extending = new $this->extending (array (
				Model_Scheme::keyField ($this->extending)	=> $this->id
			));

			$extending->save (true);
		}

		return $extending;
	}

	/**
	 * @see Model::modelName
	 * @return string
	 */
	public function modelName ()
	{
		return get_class ($this);
	}

	/**
	 * @desc Удаление контента
	 * @return boolean
	 */
	public function remove ()
	{
		if (!$this->checkAcl (__METHOD__))
		{
			return false;
		}

		$this->delete ();

		return true;
	}

	/**
	 * @see Model::title
	 * @return string
	 */
	public function title ()
	{
		return $this->base ()->title . ' ' . $this->base ()->url;
	}
}
