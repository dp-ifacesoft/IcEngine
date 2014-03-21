<?php

{if $extends && $with_load}

{/if}
/**
 * @desc {$comment}
 * Created at: {$date}
 * @author {$author}
{if !empty($properties)}
{foreach from=$properties item="property"}
 * @property {$property.type} ${$property.field} {$property.comment}
{/foreach}
{/if}
 * @package {$package}
 * @category {$category}
 * @copyright {$copyright}
 */
class {$name} extends {$extends}
{

}