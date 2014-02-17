<form name="{$form->name}" {foreach from=$form->attributes item='attr' key='key'} "{$key}"="{$attr}" {/foreach}>
    {$content}
</form>