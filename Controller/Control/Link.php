<?php

class Controller_Controll_Link extends Controller_Abstract
{
	public function _acl ()
	{
		$admin_role = Acl_Role::byName ('admin');
		$user = User::getCurrent ();

		if (!$user->hasRole ($admin_role))
		{
			return $this->replaceAction ('Error', 'accessDenied');
		}
	}

	public function __construct ()
	{
		$this->_acl ();
	}


	/**
	 *
	 * @desc Получает коллекцию моделей,
	 * имя которых было передано.
	 * Сюда же можно передать индетификатор и название
	 * другой модели. В таком случае элементы коллекции
	 * будут иметь пометку связана ли модель с коллекцией
	 */
	public function collection ()
	{
		$model = $this->_input
			->receive ('model');

		$className = $model.'_Collection';

		if (Loader::load ($className))
		{
			$collection = new $className;

			list (
				$fromTable,
				$fromRowId
			) = $this->_input->receive (
				'fromTable',
				'fromRowId'
			);

			if ($fromTable && $fromRowId)
			{
				$joineds = Helper_Link::linkedItems (
					new Model_Proxy (
						$fromTable,
						array (
							'id'	=> $fromRowId
						)
					),
					$model
				);

				if ($joineds)
				{
					foreach ($joineds as $item)
					{
						$collection_item = $collection->has ($item);
						if ($collection_item)
						{
							$collection_item->data ('joined', 1);
						}
					}
				}

			}

			$this->_output
				->send ('collection', $collection);
		}
	}

	public function index ()
	{
		$this->_output
			->send ('models', Helper_Mysql::getModels ());
	}

	public function link ()
	{
		list (
			$fromTable,
			$fromRowId,
			$toTable,
			$toRowId
		) = $this->_input->recieve (
			'fromTable',
			'fromRowId',
			'toTable',
			'toRowId'
		);

		$model_proxy = new Model_Proxy (
			$fromTable,
			array (
				'id'	=> $fromRowId
			)
		);

		Helper_Link::unlinkWith (
			$model_proxy,
			$toTable
		);

		$toRowIds = (array) explode (',', $toRowId);

		for ($i = 0, $icount = sizeof ($toRowIds); $i < $icount; $i++)
		{
			Helper_Link::link (
				$model_proxy,
				new Model_Proxy (
					$toTable,
					array (
						'id'	=> trim ($toRowIds [$i])
					)
				)
			);
		}
	}
}