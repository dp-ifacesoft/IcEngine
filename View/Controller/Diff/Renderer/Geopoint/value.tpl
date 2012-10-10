<span class="yandex-geopoint">
{if is_object($value) || is_array($value)}
    {assign var="geopoint" value=$value.0}
{else}
    {assign var="geopoint" value=$value}
{/if}
{$geopoint}
</span>
