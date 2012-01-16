<?php

namespace Ice;

Loader::load ('View_Render_Abstract');

/**
 *
 * @desc Фабрика рендеров.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class View_Render extends Model_Factory
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Factory::table()
	 */
	public function table ()
	{
		return 'View_Render';
	}

}