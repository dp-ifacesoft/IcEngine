<?php
/**
 *
 * @desc Контроллер для загрузки js ресурсов.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_View_Resource_Jres extends Controller_Abstract
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
				View_Resource_Manager::JRES
			);
		}

		$jreses = View_Resource_Manager::getData (
			View_Resource_Manager::JRES
		);

		$packer = View_Resource_Manager::packer (View_Resource_Manager::JRES);

		$packer->pack ($jreses, $config->packed_file);

		$this->_output->send (array (
			'url'	=> $config->packed_url,
			'ts'	=> $packer->cacheTimestamp ()
		));
	}

}