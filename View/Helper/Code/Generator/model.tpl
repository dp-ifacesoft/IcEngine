<?php

/**
 * {$comment}
 *
 * Created at: {$date}
{if $author}
 * @author {$author}{/if}
 * @Orm\Entity
 */
class {$name} extends {$extends}
{
    {if !empty($properties)}

    {foreach from=$properties item="property"}
/**
    {if $property.comment}
 * {$property.comment|stripslashes}
     *
    {/if}
 * @Orm\Field\{$property.type}{if $property.output}({$property.output}){/if}
     {if $property.indexes}
     {foreach from=$property.indexes item="columns" key="type"}

     * @Orm\Index\{$type}{if count($columns) > 1}({foreach from=$columns item="column" name="columns"}"{$column}"{if !$smarty.foreach.columns.last}, {/if}{/foreach}){/if}
     {/foreach}
     {/if}

     */
    public ${$property.field};

    {/foreach}
    {/if}

}