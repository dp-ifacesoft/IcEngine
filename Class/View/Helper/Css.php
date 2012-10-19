<?php
/**
 *
 * @desc Помощник представления для работы с Css
 * @author Юрий Шведов
 * @package IcEngine
 * @deprecated Следует использовать Controller_View_Resource_Css
 *
 */
class View_Helper_Css extends View_Helper_Abstract
{

	/**
	 * @desc Шаблон вставки стиля.
	 * @var string
	 */
	const TEMPLATE =
		"\t<link href=\"{\$url}?{\$ts}\" rel=\"stylesheet\" type=\"text/css\" />\n";

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

		$csses = $this->_view->resources ()->getData (
			View_Resource_Manager::CSS
		);

		$result = '';

		if ($config->packed_file)
		{
			$packer = $this
				->_view
				->resources ()
				->packer (View_Resource_Manager::CSS);

			$packer->pack ($csses, $config->packed_file);

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
			foreach ($csses as $css)
			{
				$result .= str_replace (
					array (
						'{$url}',
						'{$ts}'
					),
					array (
						$css ['href'],
						$css->filemtime ()
					),
					self::TEMPLATE
				);
			}
		}

		return $result;
	}

}