{if isset($css) && $css}
	<link href="{$css}?{$ts}" rel="stylesheet" type="text/css" />
{/if}
{if isset($csses) && $csses}
	{foreach from=$csses item="css"}
		<link href="{$css.href}" rel="stylesheet" type="text/css" />
	{/foreach}
{/if}
