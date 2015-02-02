{assign var="cdir" value="Controller/Html/Form/Field"}
{if !empty($field) && is_a($field, 'Html_Form_Field_Select')}
    <label>
        {$field->getLabel()}
        <select name="{$field->getName()}" class="form-control">
            {foreach from=$field->getOptions() item="option"}
                {include file="{$cdir}/select/option.tpl" option=$option}
            {/foreach}
        </select>
    </label>
{/if}