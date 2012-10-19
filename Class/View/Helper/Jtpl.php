<?php
/**
 *
 * @desc Помощник для подключения js шаблонов
 * @author Юрий
 * @package IcEngine
 * @deprecated Следует использовать Controller_View_Resource_Jtpl
 *
 */
class View_Helper_Jtpl extends View_Helper_Abstract
{

	/**
	 * @desc Шаблон вставки
	 * @var string
	 */
	const TEMPLATE =
		"\t<script type=\"text/javascript\" src=\"{\$url}?{\$ts}\"></script>\n";

	/**
	 * (non-PHPdoc)
	 * @see View_Helper_Abstract::get()
	 */
	public function get (array $params)
	{
		$config = $this->config ();

		$sources = $config->sources;

		foreach ($config->sources as $source)
		{
			View_Resource_Loader::load (
				$source ['base_url'],
				$source ['base_dir'],
				$source ['patterns'],
				View_Resource_Manager::JTPL
			);
		}

		$tpls = $this->_view->resources ()->getData (
			View_Resource_Manager::JTPL
		);

		$result = '';

		$packer = $this
			->_view
			->resources ()
			->packer (View_Resource_Manager::JTPL);

		$packer->pack ($tpls, $config->packed_file);

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

		return $result;
	}

}