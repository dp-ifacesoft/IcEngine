<?php

/**
{if isset($comment)}@desc {$comment}{/if}
 *
{if isset($date)}
 * Created at: {$date}
{/if}
{if isset($package)}
 * @package {$package}
{/if}
{if isset($copyright)}
 * @copyright {$copyright}
{/if}
{if isset($author)}
 * @author {$author}
{/if}
 * @Service("{$serviceName}")
 */
class Service_{$name} extends Service_Abstract
{

}
