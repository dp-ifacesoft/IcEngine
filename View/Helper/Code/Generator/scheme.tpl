<?php

$config = array (
	{if !empty($admin_panel)}
		{$admin_panel},
	{/if}

	{if $comment}
'comment'		=> '{$comment|addslashes}',
	{/if}
{if $author}'author'		=> '{$author|addslashes}',
	{/if}
'fields'		=> array (
	{foreach from=$fields item="field" name="fields" key="name"}
	'{$name}'	=> array (
			'{$field[0]}'{if !empty($field[1])},

			array (
				{foreach from=$field[1] item="f" name="field1" key="field_name"}{if !$smarty.foreach.field1.first}	{/if}{if is_numeric($field_name)}'{$f}'{if !$smarty.foreach.field1.last},{/if}{else}{if !is_array($f)}'{$field_name}'	=> {if is_null($f)}null{elseif is_numeric($f)}{$f}{elseif $field[0]=='Enum'}{$f}{else}'{$f|addslashes}'{/if}{if !$smarty.foreach.field1.last},{/if}{else}'{$field_name}'	=> array ({foreach from=$f item="ff" name="fff"}{if $field[0]=='Enum'}{$ff}{else}{$ff|addslashes}{/if}{if !$smarty.foreach.fff.last}, {/if}{/foreach}){if !$smarty.foreach.field1.last},{/if}{/if}{/if}

			{/foreach}){/if}

		){if !$smarty.foreach.fields.last},{/if}

	{/foreach}
){if !empty($indexes)},{/if}

	{if !empty($indexes)}
'indexes'		=> array (
		{foreach from=$indexes item="index" name="indexes" key="name"}
'{$name}'	=> array (
			'{$index[0]}',
			array ({foreach from=$index[1] item="i" name="index1"}'{$i}'{if !$smarty.foreach.index1.last}, {/if}{/foreach})
		){if !$smarty.foreach.indexes.last},{/if}

		{/foreach}

	){if (!empty($indexes) || !empty($fields)) && !empty($references)},{/if}
{/if}

	{if !empty($references)}
'references'	=> array (
		{foreach from=$references item="reference" name="references" key="name"}
'{$name}'	=> array (
			{foreach from=$reference item="r" name="reference1"}'{$r}'{if !$smarty.foreach.reference1.last}, {/if}{/foreach}
		){if !$smarty.foreach.references.last},
		{/if}

			{/foreach}

	)
{/if}

);
