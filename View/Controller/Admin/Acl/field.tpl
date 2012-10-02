<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />

	<div class="title">
		<h2>Таблицы</h2>
	</div>

	{if $tables}
	<form method="post" action="/cp/acl/role/save/">
		<input type="hidden" name="role_id" value="{$role_id}" />
		{foreach from=$tables item="i"}
		<br /><h2 style="font-size:17px !important; font-weight:bold"><input type="checkbox" onclick="Controller_Admin_Database.multiCheck ($(this), 'Table/{$i.table->Name}/');" />{$i.table->Name}{if $i.table->Comment} ({$i.table->Comment}){/if}</h2><br />
			{foreach from=$i.fields item="j"}
			<li>
				<span style="padding:2px; background:#d0d0d0">
					{foreach from=$j.resources item="k"}
						<input type="checkbox" name="resources[]" value="{$k.resource}"{if $k.on} checked{/if} />
						{$k.type}
					{/foreach}
				</span>&nbsp;

				{$j.field->Field}{if $j.field->Comment} ({$j.field->Comment}){/if}
			</li>
			{/foreach}
		</ul>
		{/foreach}
		<br />
		<input type="submit" value="Применить" />
	</form>
	{/if}

</div>

<script type="text/javascript">
	$(function () {
		$("input[name='resources[]']").live ('change', function () {
			Controller_Admin_Acl.saveOneResource ($(this).val(), $(this).is(':checked'), $("input[name='role_id']").val());
		});
	});
</script>