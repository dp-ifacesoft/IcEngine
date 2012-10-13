<table>
	<thead>
		<tr>
			<th>id</th>
			<th>start time</th>
			<th>last active</th>
			<th>state</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$processes item=process}
			<tr id="Backgroun_Process_{$process->id}">
				<td valign="top">{$process->id}</td>
				<td valign="top">{$process->startTime}</td>
				<td valign="top">{$process->lastActiveTime}</td>
				<td valign="top">{$prcoess->Background_Process_State->name}</td>
				<td valign="top">
					<a href="javascript:void(0);">reset</a><br />
					<a href="javascript:void(0);">resume</a>
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>