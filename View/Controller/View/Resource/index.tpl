{foreach from=$reses key=name item=res}
	{if $res.url}
		{assign var="cdir" value=$smarty.current_dir}
		{include file="$cdir/index/`$res.type`.tpl"}
	{/if}
{/foreach}