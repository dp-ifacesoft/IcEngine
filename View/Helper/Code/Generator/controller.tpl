<?php

/**
 * 
{if isset($comment)}@desc {$comment}{/if}
 *
{if isset($date)}
 * Created at: {$date}
{/if}
{if isset($author)}
 * @author {$author}
{/if}
 * @category Controllers
{if isset($package)}
 * @package {$package}
{/if}
{if isset($copyright)}
 * @copyright {$copyright}
{/if}
 */
class Controller_{$name} extends Controller_Abstract
{
        {if isset($actions)}
	{foreach from=$actions item="action"}
        /**
	 * 
         *
	 */
	public function {$action} ()
	{

	}
	{/foreach}
        {/if}

}