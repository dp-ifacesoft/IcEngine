<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />
	
	<div class="title">
		<h2>Роли</h2>
	</div>
	
	{if $role_collection->count()}
	<ul>
		{foreach from=$role_collection item="i"}
		<li><a href="/cp/acl/role/{$i->key()}/">{$i->name}</a></li>
		{/foreach}
	</ul>
	{/if}
	
</div>