<?php
/**
 *
 * @desc Контроллер для работы с Css
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_View_Resource_Css extends Controller_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		$config = $this->config ();

		if ($config->dirs)
		{
			View_Resource_Manager::load (
				$config->base_url,
				$config->base_dir,
				$config->dirs,
				View_Resource_Manager::CSS
			);
		}
		else
		{
			foreach ($config->sources as $source)
			{
				View_Resource_Loader::load (
					$source ['base_url'],
					$source ['base_dir'],
					$source ['patterns'],
					View_Resource_Manager::CSS
				);
			}
		}

		$csses = View_Resource_Manager::getData (View_Resource_Manager::CSS);

		if ($config->packed_file)
		{
			$packer = View_Resource_Manager::packer (
				View_Resource_Manager::CSS
			);

			$packer->pack ($csses, $config->packed_file);

			$this->_output->send (array (
				'css'	=> $config->packed_url,
				'ts'	=> $packer->cacheTimestamp ()
			));
		}
		else
		{
			$this->_output->send ('csses', $csses);
		}
	}

}