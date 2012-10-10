Картинка #{$field->id}
{assign var="imgUrl" value=$field->getUrl('small')}
{if $imgUrl}<br/><img src="{$imgUrl}"/><br/>{/if}