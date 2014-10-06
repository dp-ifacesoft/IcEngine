<?php

/**
 * Упаковщик Jtpl ресурсов представления.
 * 
 * @author goorus, morph
 */
class View_Resource_Packer_Jtpl extends View_Resource_Packer_Js
{
	/**
     * @inheritdoc
     */
	public function packOne(View_Resource $resource)
	{
		$config = $this->config();
		if ($config->item_prefix && isset($resource->filePath)) {
			$result = strtr($config->item_prefix, array(
                '{$source}' => $resource->filePath,
                '{$src}'	=> $resource->localPath,
            ));
		} else {
			$result = '';
		}	
		$replacedContent = str_replace(
			array('\\',	'"', "\r\n", "\n", "\r"),
			array('\\\\', '\\"', '"+"\\r\\n"+"', '"+"\\n"+"', '"+"\\r"+"'),
			$resource->content ()
		);
		$content = 'View_Render.templates[\'' . $resource->localPath . '\']="' . 
            $replacedContent . '";';
		if (!empty($this->currentResource->nopack) || $this->noPack) {
			$result .= $content . "\n";
		} else {
			$packer = new JavaScriptPacker($content, 0);
			$result .= $packer->pack();
	    }
		return $result . $this->config()->item_postfix;
	}
}