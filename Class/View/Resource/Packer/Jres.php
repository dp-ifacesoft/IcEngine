<?php

/**
 * Упаковщик Jres ресурсов представления.
 * 
 * @author goorus, morph
 */
class View_Resource_Packer_Jres extends View_Resource_Packer_Js
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
		$content = 'Ice.Resource_Manager.set("Jres", "' . 
            $resource->localPath . '", ' . 
            $resource->content() . ');';
		if (!empty($this->currentResource->nopack) || $this->noPack) {
			$result .= $content . "\n";
		} else {
			$packer = new JavaScriptPacker($content, 0);
			$result .= $packer->pack();
	    }
		return $result . $this->config()->item_postfix;
	}
}