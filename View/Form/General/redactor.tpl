{$data = $element->data()}
<textarea id="text-{$element->name}" name="{$element->name}" {foreach from=$element->attributes item='attr' key='key'}{$key}="{$attr}" {/foreach} />{$element->value}</textarea>
<script type="text/javascript">
    Call_List.append('Form_Element_Redactor', 'init', [{
        id: 'text-{$element->name}',
        template: '{$data.template}'
    }]);
</script>