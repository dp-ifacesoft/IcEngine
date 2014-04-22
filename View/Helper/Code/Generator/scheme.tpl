<?php

return array (
    {if !empty($admin)}
		'admin' => {$admin},
	{/if}

    {if !empty($languageScheme)}
		'languageScheme' => {$languageScheme},
	{/if}
    {if !empty($geoFields)}
        'geoFields' => {$geoFields},
    {/if}
    {if !empty($signals)}
		'signals' => {$signals},
	{/if}

    {if !empty($createScheme)}
		'createScheme' => {$createScheme},
	{/if}

	{if $comment}
        'comment'		=> '{$comment|addslashes}',
	{/if}
    {if $author}
        'author'		=> '{$author|addslashes}',
	{/if}
    {$serviceLocator=IcEngine::serviceLocator()}
    {$helperConverter=$serviceLocator->getService('helperConverter')}
    {$fields=$helperConverter->arrayToString($fields)}
    'fields'            => {$fields}{if !empty($indexes)},{/if}
	{if !empty($indexes)}
        {$indexes=$helperConverter->arrayToString($indexes)}
        'indexes'		=> {$indexes}{if !empty($references)},{/if}
    {/if}
    {if !empty($references)}
        {$references=$helperConverter->arrayToString($references)}
        'references'    => $references
    {/if}
);
