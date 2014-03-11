<ul class="Breadcrumbs">
    {foreach from=$path item=route name="breadcrumb"}
    	<li>
    		<a href="{$route->link()}">{$route->title}</a>
    		{if !$smarty.foreach.breadcrumb.last}
    			&em;
    		{/if}
    	</li>
    {/foreach}
</ul>