{Controller call="Yandex_Map/includeScript"}
{if $row->key()}
{Controller call="Admin_Database_Map/index" table=$table row_id=$row->key()}
{/if}
