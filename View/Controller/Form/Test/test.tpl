{form instance=$form}{/form}
{*
    <div class="sdfsdf">test</div>
    {form_element form=$form name="author"}<br>
    {form_element form=$form name="title"}
*}
{form instance=$form2}
    <div class="sdfsdf">test2</div>
    {form_element form=$form2 name="author"}<br>
    {form_element form=$form2 name="title"}
{/form}