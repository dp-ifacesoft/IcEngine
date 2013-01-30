<?php
/**
 *
 * @desc Помощник для подключения js скриптов
 * @author Юрий
 * @package IcEngine
 * @deprecated Следует использовать Controller_View_Resource_Js
 *
 */
class View_Helper_Js extends View_Helper_Abstract
{

	/**
	 * @desc Шаблон вставки
	 * @var string
	 */
	const TEMPLATE =
		"\t<script type=\"text/javascript\" src=\"{\$url}?{\$ts}\"></script>\n";

	public function get (array $params)
	{
		$config = $this->config ();
	
		if (isset ($config->dirs))
		{
			View_Resource_Loader::load (
				$config->base_url,
				$config->base_dir,
				$config->dirs
			);
		}
		else
		{
			foreach ($config->sources as $source)
			{
				View_Resource_Loader::load (
					$source ['base_url'],
					$source ['base_dir'],
					$source ['patterns']
				);
			}
		}

		$jses = $this->_view->resources()->getData (
			View_Resource_Manager::JS
		);

		$result = '';

		if ($config->packed_file)
		{
			$packer = $this
				->_view
				->resources ()
				->packer (View_Resource_Manager::JS);

			$packer->pack ($jses, $config->packed_file);

			$result = str_replace (
				array (
					'{$url}',
					'{$ts}'
				),
				array (
					$config->packed_url,
					$packer->cacheTimestamp ()
				),
				self::TEMPLATE
			);
		}
		else
		{
			foreach ($jses as $js)
			{
				$result .= str_replace (
					array (
						'{$url}',
						'{$ts}'
					),
					array (
						$js ['href'],
						$js->filemtime ()
					),
					self::TEMPLATE
				);
			}
		}

		return $result;
	}

}