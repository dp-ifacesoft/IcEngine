<?php
/**
 *
 * @desc Контроллер для вывода шаблонов js.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_View_Resource_Jtpl extends Controller_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		$config = $this->config ();

		$sources = $config->sources;

		foreach ($config->sources as $source)
		{
			View_Resource_Manager::load (
				$source ['base_url'],
				$source ['base_dir'],
				$source ['patterns'],
				View_Resource_Manager::JTPL
			);
		}

		$tpls = View_Resource_Manager::getData (
			View_Resource_Manager::JTPL
		);
		$packer = View_Resource_Manager::packer (View_Resource_Manager::JTPL);

		$packer->pack ($tpls, $config->packed_file);

		$this->_output->send (array (
			'url'	=> $config->packed_url,
			'ts'	=> $packer->cacheTimestamp ()
		));
	}

}