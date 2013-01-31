<?php
/**
 *
 * @desc Упаковщик Js ресурсов представления.
 * @author Юрий
 * @package IcEngine
 *
 */
class View_Resource_Packer_Js extends View_Resource_Packer_Abstract
{
	/**
	 * @desc Класс Packer'a
	 * @var string
	 */
	const PACKER = 'class.JavaScriptPacker.php';

	public function __construct ()
	{
		Loader::requireOnce (self::PACKER, 'includes');
	}

	public function packOne (View_Resource $resource)
	{
		if (
			$this->config ()->item_prefix &&
			isset ($resource->filePath)
		)
		{
			$result = strtr (
				$this->config ()->item_prefix,
				array (
					'{$source}' => $resource->filePath,
					'{$src}'	=> $resource->localPath
				)
			);
		}
		else
		{
			$result = '';
		}

		if ($this->_currentResource->nopack)
		{
			$result .= $resource->content ();
		}
		else
		{
//			$result .=
//				preg_replace (
//					'#\n\s*/\*.*\*/#Us',
//					"\n",
//					$resource->content ()
//				);
			$packer = new JavaScriptPacker ($resource->content (), 0);
			$result .= $packer->pack ();
			//$result .= $resource->content();

		}

		$fname = IcEngine::root () . 'cache/js.pack.log';
		file_put_contents ($fname, time() . PHP_EOL, FILE_APPEND);

		return $result . $this->config ()->item_postfix;
	}

}
