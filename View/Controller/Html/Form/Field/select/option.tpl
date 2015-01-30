{if !empty($option) && is_a($option, 'Html_Form_Field_Select_Option')}
    <option value="{$option->getValue()}" {if $option->isSelected()}selected{/if}>{$option->getText()}</option>
{/if}