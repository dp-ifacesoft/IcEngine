{literal}
<style type="text/css">
	#content-admin .child
	{
		margin-left:20px;
		margin-top:8px;
		font-size:14px;
	}
		#content-admin .child span
		{
			border: none !important;
		}
	#content-admin .parent
	{
		font-size:16px;
		padding-bottom:8px;
		margin-top: 4px;
		cursor: hand;
		cursor: pointer;
	}
		#content-admin .parent span
		{
			border-bottom: dashed 1px #000;
		}
</style>
{/literal}

{if $parent->parentId}
	<div class="title-zakaz">
		<div class="nav-back"><a href="/admin/"><span>&larr;</span>&nbsp;Вернуться к разделу администрирования</a></div>
	</div>
{/if}
<div class="infoBlockText" id="content-admin">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />

	<div class = "title">
		<h2>Разделы контента</h2>
	</div>

	{if $categories->count()}
		<ul id="tree">
			{foreach from=$categories item="category"}
				<li class="level-0 parent">
					<span onclick="Controller_Admin_Content_Category.toggleSubcategories(this,{$category->key()},0)" id="category-{$category->key()}">{$category->title}</span>
				</li>
			{/foreach}
		</ul>
	{/if}
</div>