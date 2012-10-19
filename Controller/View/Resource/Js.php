<?php
/**
 *
 * @desc Контроллер для подключения js скриптов
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_View_Resource_Js extends Controller_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		$config = $this->config ();

		if ($config->sources)
		{

			// Старый способ сбора скриптов
			foreach ($config->sources as $source)
			{
				View_Resource_Manager::load (
					$source ['base_url'],
					$source ['base_dir'],
					$source ['patterns'],
					View_Resource_Manager::JS
				);
			}

			$jses = View_Resource_Manager::getData (View_Resource_Manager::JS);

			if ($config->packed_file)
			{
				$packer = View_Resource_Manager::packer (
					View_Resource_Manager::JS
				);

				$packer->pack ($jses, $config->packed_file);

				$this->_output->send (array (
					'url'	=> $config->packed_url,
					'ts'	=> $packer->cacheTimestamp ()
				));
			}
			else
			{
				$this->_output->send ('jses', $jses);
			}
		}

		if ($config->targets)
		{
			// Новый способ сбора скриптов
			foreach ($config->targets as $target)
			{
				$params = $this->_input->receive ('params');
				$vars = array ();
				foreach ($params as $k => $v)
				{
					$vars ['{$' . $k . '}'] = $v;
				}

				$url = strtr ($target->url, $vars);
				$dst_file = strtr ($target->file, $vars);
				$jses = array ();

				foreach ($target->sources as $source)
				{
					$src_dir = strtr ($source->dir, $vars);
					$src_files = is_scalar ($source->file)
						? array ($source->file)
						: $source->file->__toArray ();

					foreach ($src_files as $src_file)
					{
						$src_file = strtr ($src_file, $vars);
						$jses [$src_file] = array_merge (
							$jses,
							View_Resource_Manager::patternLoad (
								$src_dir,
								$src_file,
								View_Resource_Manager::JS
							)
						);
					}
				}
			}
		}

	}

}