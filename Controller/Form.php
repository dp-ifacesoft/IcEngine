<?php

class Controller_Form extends Controller_Abstract
{
	const 	READ			= 	'read';
	const 	EDIT			= 	'edit';
	const 	CREATE 			= 	'create';
	const 	DELETE			= 	'delete';

	const 	PARAMS			= 	'param';

	const	BEFORE_CREATE	=	'beforeCreate';
	const	AFTER_CREATE	=	'afterCreate';

	const	BEFORE_EDIT		=	'beforeEdit';
	const 	AFTER_EDIT		=	'afterAfter';

	const 	BEFORE_DELETE	=	'beforeDelete';
	const	AFTER_DELETE	=	'afterDelete';

	const	BEFORE_VIEW		=	'beforeView';
	const	AFTER_VIEW		=	'afterView';

	const	BEFORE_ROLL		=	'beforeRoll';
	const	AFTER_ROLL		=	'afterRoll';

	public $scheme;

	protected function _accessDenied ()
	{
		return $this->replaceAction ('Error', 'accessDenied');
	}

	protected function _afterCreate ($model, $params)
	{

	}

	protected function _afterDelete ($model, $params)
	{

	}

	protected function _afterEdit ($model, $params)
	{

	}

	protected function _beforeCreate ($model, $params)
	{

	}

	protected function _beforeDelete ($model, $params)
	{

	}

	protected function _beforeEdit ($model, $params)
	{

	}

	public function _updateModel ($model, $params)
	{
		array (
			'id'		=> array (
				'type' 			=> 'integer',
				'default' 		=> -1,
				'attribute'		=> true
			),
			'name'		=> array (
				'type' 	=> 'string',
				'roles'	=> array (
					'Admin',
					'Agency'
				)
			),
			'User__id'	=> array (
				'model' 	=> 'User',
				'linked'	=> true,
				'value' 	=> User::getCurrent ()->id (),
				'triggers'	=> array (
					self::BEFORE_CREATE	=> array (),//new Controller_User, 'create'),
					self::AFTER_CREATE	=> array ($this, 'create'),
					self::BEFORE_EDIT	=> array (),
					self::AFTER_EDIT	=> array (),
					self::BEFORE_DELETE	=> array (),
					self::AFTER_DELETE	=> array (),
					self::BEFORE_VIEW	=> array (),
					self::AFTER_VIEW	=> array (),
					self::BEFORE_ROLL	=> array (),
					self::AFTER_ROLL	=> array ()
				)

			)

		);

		$user = User::getCurrent ();

		$new_once = false;

		if (!$model->key ())
		{
			$this->_beforeCreate ($model, $params);
			$new_one = true;
		}
		else
		{
			$this->_beforeEdit ($model, $params);
		}

		foreach ((array) $this->scheme as $key=>$stat)
		{
			$user_can = false;
			if (!empty ($stat ['roles']))
			{
				$roles = array_values ($stat ['roles']);
				for ($i = 0, $icount = sizeof ($roles); $i < $icount; $i++)
				{
					$class_name = 'Acl_Role_Type_'.$roles [$i];
					$collection = Helper_Link::linkedItems (
						$user,
						'Acl_Role'
					);
					$collection
						->where ('Acl_Role_Type__id=?', constant ("$class_name::ID"));
					if ($collection->first ())
					{
						$user_can = true;
						break;
					}
				}
			}
			if (!$user_can)
			{
				continue;
			}
			if (empty ($params [$key]))
			{
				$params [$key] = !empty ($stat ['value']) ? $stat ['value'] : '';
				if (!isset ($stat ['default']))
				{
					$params [$key] = $stat ['default'];
				}
			}
			$current = $model;
			if (isset ($stat ['model']))
			{
				$current->save ();
				$current = $model->field ($stat ['model']);

			}
			if (!empty ($stat ['attribute']))
			{
				$current->attr ($key, $params [$key]);
			}
			else
			{
				$current->field ($key, $params [$key]);
			}
			if ($current !== $model && !empty ($stat ['linked']))
			{
				Helper_Link::link (
					$model,
					$current
				);
			}
		}
		$current->save ();

		if ($new_one)
		{
			$this->_afterCreate ($model, $params);
		}
		else
		{
			$this->_afterEdit ($model, $params);
		}

	}

	/**
	 *
	 * @param array|string $resource
	 * @return boolean
	 */
	protected function _userCan ($resource)
	{
		if (is_array ($resource))
		{
			$resource = Acl_Resource::byNameCheck ($resource);
		}
		else
		{
			$resource = Acl_Resource::byNameCheck (func_get_args ());
		}

		return ($resource && $resource->userCan (User::getCurrent ()));
	}

	public function view ()
	{
		$id = $this->_input->receive ('id');
		$model_name = $this->name ();

		if (!$this->_userCan ($model_name, $id, self::READ))
		{
			return $this->_accessDenied ();
		}

		$model = Model_Manager::get ($model_name, $id);

		$this->_output->send ('model', $model);
	}

	public function create ()
	{
		$id = 0;
		$model_name = $this->name ();

		if (!$this->_userCan ($model_name, $id, self::READ))
		{
			return $this->_accessDenied ();
		}

		// TODO: filters
		$params = $this->_input->receive (self::PARAM);

		$key_field = Model_Scheme::keyField ($model_name);

		if (isset ($params [$key_field]))
		{
			unset ($params [$key_field]);
		}

		$model = new $model_name (array (
			$key_field => null
		));

		$this->_updateModel ($model, (array) $params);

		$this->_output->send (array (
			'model'	=> $model
		));
	}

	public function edit ()
	{
		$id = $this->_input->receive ('id');
		$model_name = $this->name ();

		if (!$this->_userCan ($model_name, $id, self::EDIT))
		{
			return $this->_accessDenied ();
		}

		// TODO: filters
		$params = $this->_input->receive (self::PARAM);

		$key_field = Model_Scheme::keyField ($model_name);

		if (isset ($params [$key_field]))
		{
			unset ($params [$key_field]);
		}

		$model = Model_Manager::byKey ($model_name, $id);

		if (!$model)
		{
			return $this->replaceAction ('Error', 'notFound');
		}

		$this->_updateModel ($model, (array) $params);

		$this->_output->send (array (
			'model'	=> $model
		));
	}

	public function delete ()
	{
		$ids = (array) $this->_input->receive ('id');
		$model_name = $this->name ();

		foreach ($ids as $id)
		{
			if (!$this->_userCan ($model_name, $id, self::DELETE))
			{
				return $this->_accessDenied ();
			}
		}

		$key_field = Model_Scheme::keyField ($model_name);

		$collection = Model_Collection_Manager::byQuery (
			$model_name,
			Query::instance ()
			->where ("$key_field IN (?)", $ids)
		);

		$collection->delete ();

		$this->_output->send (array (
			'count'	=> $collection->count ()
		));
	}

	/**
	 * Форма устарела
	 */
	public function obsolete ()
	{

	}



}