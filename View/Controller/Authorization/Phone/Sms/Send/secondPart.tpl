{if !$registered}<div id="type_hint">Пользователя с таким телефоном ещё не зарегестрировано.</div>{/if}
<div class="capt"><b>КОД ПОДТВЕРЖДЕНИЯ</b>:</div>
<div>
	<input type="button" value="Отправить код" style="width: 200px;" class="send_sms_button" onclick="Controller_Authorization_Phone_Sms_Send.sendSmsCode ($(this).closest ('form'));" />
</div>
<div class="result-msg hidden"></div>
<div class="btns_block">
	{if $registered}
		<a class="submit btn_green"><span>ВХОД</span></a>
	{else}
		<a class="submit btn_green"><span>РЕГИСТРАЦИЯ</span></a>
	{/if}
</div>