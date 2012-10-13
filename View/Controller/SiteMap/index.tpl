<h1>Карта сайта</h1> 
<ul class="SiteMap">
    {foreach from=$map item=item}
    	<li style="padding-left:{$item->level(45)}px;">
    		<a href="{$item->link()}">{$item->title}</a>
    	</li>
    {/foreach}
</ul>