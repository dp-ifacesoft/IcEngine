{assign var="cdir" value="Controller/Html/Form"}
{if !empty($form) && is_a($form, 'Html_Form')}
    <form>
        {foreach from=$form->getReady() item="field"}
            {if is_a($field, 'Html_Form_Field')}
                <div class="form-group">
                    {include file="{$cdir}/Field/{$field->getType()}.tpl" field=$field}
                </div>
            {/if}
        {/foreach}
        <div class="form-group">
            <input type="submit" class="btn btn-default" value="Отправить">
        </div>
    </form>
{/if}