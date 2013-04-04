<style type="text/css">
	#profiling ul {
		list-style-type: disc;
		margin:10px 0 20px 10px;
	}
	#profiling ul li {
		margin-bottom:10px;
	}
</style>

<div id="profiling" style="margin-top:20px">
	<h2>Результаты профайлинга</h2>
	<br />
	<p><b>Общее время работы приложения:</b> {$totalTime} с.</p><br />
	<p><b>Время роутинга:</b> {$routingTime} с.</p>
	<p><b>Время бутстрапинга:</b> {$bootstrapTime} c. </p>
	<ul>
		<li>Инициализация БД: {$bootstrapInitDb} с.</li>
		<li>Инициализация менеджера атрибутов: {$bootstrapInitAttributeManager} с.</li>
		<li>Инициализация схемы моделей: {$bootstrapInitModelScheme} с. </li>
		<li>Инициализация менеджера моделей: {$bootstrapInitModelManager} с.</li>
		<li>Инициализация пользователей: {$bootstrapInitUser} с. </li>
	</ul>
	<p><b>Время инициализации сессии пользователя:</b> {$bootstrapInitUserSession} с.</li>
	<p><b>Время диспетчеризации:</b> {$dispatcherTime} с. </p>
	<p><b>Время работы фронт контроллера:</b> {$frontControllerTime} с. </p>
    <p><b>Время работы стратегии фронт контроллера:</b> {$controllerFrontStrategyTime} с. </p>
	<p><b>Общее время рендеринга:</b> {$renderTime} с. </p>
	<p><b>Всего загружено классов:</b> {$loadedClassCount}</p>
	<p><b>Всего создано моделей:</b> {$totalModelCount}</p>
    {if $models}
        <p><b>Созданые модели:</b></p>
        <ul>
            {foreach from=$models item="model"}
                <li>{$model->table()}, {$model->key()}</li>
            {/foreach}
        </ul>
    {/if}
	<p><b>Всего вызвано контроллеров:</b> {$controllerCount + $cachedControllerCount}, из них из кэша: <b>{$cachedControllerCount}</b></p>
	<br />
	<p><b>Всего select запросов к БД:</b> {$selectQueryCount}, из них из кэша: <b>{$cachedSelectQueryCount}</b>,
		за <b>{$selectQueryTime + $lowQueryTime} с.</b></p>
	<p><b>Медленных запросов:</b> {$lowQueryCount}</p>
	{if $lowQueryCount}
		<p><b>Общее время, затраченное на медленные запросы:</b> {$lowQueryTime} с. </p>
		<p><b>Медленные запросы:</b></p>
		<ul>
			{foreach from=$lowQueryVector item="query"}
				<li>{$query[0]}, <b>{$query[1]} с.</b> </li>
			{/foreach}
		</ul>
	{/if}
    {if $allQueryVector}
        <p><b>Все запросы:</b></p>
        <ul>
            {foreach from=$allQueryVector item="query"}
                <li>{$query}</li>
            {/foreach}
        </ul>
    {/if}
	<p><b>Всего запросов update к БД:</b> {$updateQueryCount}, за <b>{$updateQueryTime} с.</b></p>
	<p><b>Всего запросов insert к БД:</b> {$insertQueryCount}, за <b>{$insertQueryTime} с.</b></p>
	<p><b>Всего запросов delete к БД:</b> {$deleteQueryCount}, за <b>{$deleteQueryTime} с.</b></p>

	<br />
	<p><b>Всего запросов get к redis:</b> {$redisGetCount}, за <b>{$redisGetTime} с.</b></p>
	<p><b>Всего запросов set к redis:</b> {$redisSetCount}, за <b>{$redisSetTime} с.</b></p>
	<p><b>Всего запросов keys к redis:</b> {$redisKeyCount}, за <b>{$redisKeyTime} с.</b></p>
	<p><b>Всего запросов delete к redis:</b> {$redisDeleteCount}, за <b>{$redisDeleteTime} с.</b></p>

	<br />
	<p><b>Вызовы контроллеров:</b></p>
	<ul>
		{foreach from=$sessions item="session" key="key" name="sessions"}
			{if $session.args[0] == 'Controller_Manager'}
				<li>
					<p><b>Вызов:</b> {$session.args[3]}/{$session.args[4]}</p>
					<p>Затраченно времени: {$session.logs[0].delta} с.</p>
					<p>Создано моделей: {$session.logs[0].args[1]}</p>
					<p>Выполнено запросов не из кэша: {$session.logs[0].args[1]}</p>
					<p>Затраты памяти: {$session.logs[0].args[2]/1024/1024}/{$maxMemory}</p>
					<p>Время рендеринга: {$session.logs[0].args[3]} с.</p>
                    <p>Обращений get к redis: {$session.logs[0].args[4]} с.</p>
				</li>
			{/if}
		{/foreach}
	</ul>
	<br />
	{if $selectQueryCount != $cachedSelectQueryCount}
		<p><b>Вызовы запросов:</b></p>
		<ul>
			{foreach from=$sessions item="session" key="key" name="sessions"}
				{if $session.args[0] == 'Data_Mapper_Mysqli_Cached'}
					<li>
						<p><b>Запрос:</b> {$session.logs[0].args[0]}</p>
						<p>Затраченно времени: {$session.logs[0].delta} с.</p>
						<p>Получено рядов: {$session.logs[0].args[1]}</p>
						<p>Затраты памяти: {$session.logs[0].args[2]/1024/1024}/{$maxMemory}</p>
					</li>
				{/if}
			{/foreach}
		</ul>
	{/if}
</div>
<script type="text/javascript">
	$(function() {
		$('#profiling').appendTo($('#container'));
	});
</script>