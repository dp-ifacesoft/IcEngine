<?php

/**
 * {$comment}
 * 
 * Created at: {$date}
{if !empty($table)}
 * Imported for "{$table}"{/if}
{if $author}
 * @author {$author}{/if}
{if !empty($properties) && empty($notCommentedProperties)}
{foreach from=$properties item="property"}
 * @property {$property.type} ${$property.field} {$property.comment}
{/foreach}
{/if}
{if !empty($package)}
 * @package {$package}
{/if} 
 * @category {$category}
{if $copyright}
 * @copyright {$copyright}
{/if}
{if !empty($table)}
 * @Orm\Entity(table="{$table}")
{/if}
 */
class {$name} extends {$extends}
{   
    {if !empty($properties) && !empty($notCommentedProperties)}
    
    {foreach from=$properties item="property"}
/**
    {if $property.comment}
 * {$property.comment}
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