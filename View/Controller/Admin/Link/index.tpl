<div class="infoBlockText">
	<img src="/images_site/site/informer_left_corner.png" class="top-gray-corner-l" alt="" />
	<img src="/images_site/site/informer_right_corner.png" class="top-gray-corner-r" alt="" />
	
	<div class="title">
		<h2>Связи</h2>
	</div>
	
	{if isset($tables)}
	<p>Выберите таблицу: </p>
	<p>
		<select id="table1_id">
			<option value=""></option>
			{foreach from=$tables item="table"}
			<option value="{$table.name}"> {$table.title} {$table.name}</option>
			{/foreach}
		</select>
	</p>
	
	<div style="display:none">
		<p>Выберите объект:</p>
		<select id="model1"></select>
	</div>
	
	<div style="display:none">
		<p>Выберите таблицу, с которой будет связь: </p>
		<p>
			<select id="table2_id">
				<option value=""></option>
				{foreach from=$tables item="table"}
				<option value="{$table.name}"> {$table.title} {$table.name}</option>
				{/foreach}
			</select>
		</p>
	</div>
	
	<div style="display:none">
		<p>Выберите объекты для связи: </p>
		<div id="model2" style="width:300px; height:150px; overflow:auto"></div>
		<p><input type="button" id="model-save" value="Применить" /></p>
	</div>
	
	{/if}
	
</div>