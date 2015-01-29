<?php

/**
 {if $comment}* {$comment}
 {/if}
*
 {if $author}* @author {$author}
 {/if}
*/
class {$name} extends Vo 
{
{foreach from=$fields item="field" key='fieldName'}
    /**
{if isset($field['comment'])}     * {$field['comment']}
{/if}
     *
{if isset($field['type'])}     * @return {$field['type']}
{/if}
     */
    public function get{ucfirst($fieldName)}()
    {
        return $this->field('{$fieldName}');
    }
    
{/foreach}
}