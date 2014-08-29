<?php

/**
 * Упаковщик Js ресурсов представления.
 * 
 * @author goorus, morph
 */
class View_Resource_Packer_Js extends View_Resource_Packer_Abstract
{
	/**
	 * Класс Packer'a
	 * 
     * @var string
	 */
	const PACKER = 'class.JavaScriptPacker.php';
    
    /**
     * Не упаковывать файлы
     * 
     * @var boolean
     */
    protected $noPack = false;

    /**
     * Конструктор
     */
	public function __construct()
	{
		IcEngine::getLoader()->requireOnce(self::PACKER, 'Vendor');
	}

    /**
     * @inheritdoc
     */
	public function packOne(View_Resource $resource)
	{
        $config = $this->config();
		if ($config->item_prefix && isset($resource->filePath)) {
			$result = strtr($config->item_prefix, array (
                '{$source}' => $resource->filePath,
                '{$src}'	=> $resource->localPath
            ));
		} else {
			$result = '';
		}
		if ($this->currentResource->nopack || $this->noPack) {
			$result .= $resource->content();
		} else {
			/*ob_start();
            $command = 'java -jar ' . IcEngine::root() . 
                'Ice/Static/utils/yuicompressor.jar ' .
                $resource->filePath;
            system($command);
            $result .= ob_get_contents();
            ob_end_clean();*/
            $packer = new JavaScriptPacker($resource->content(), 0);
			$result .= $packer->pack();
		}
		return $result . $config->item_postfix;
	}
}