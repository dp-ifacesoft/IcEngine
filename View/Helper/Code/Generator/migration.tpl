<?php

/**
{if $comment}
 * {$comment}
 * 
 {/if}
 * Created at: {$createdAt}
 {if $author}
 * @author {$author}
 {/if}
 {if $category}
 * @category {$category}
 {/if}
 {if $sequence}
 * @seq {$sequence}
 * @marks
 {/if}
 */
 class Migration_{$name} extends Migration_Abstract
 {
	/**
	 * Модель, схема которой будет изменена миграцией
	 * 
     * @var string
	 */
	public $model{if $modelName} = '{$modelName}'{/if};

	/**
	 * @see Migration_Abstract::down()
	 */
	public function down()
	{
		return true;
	}

	/**
	 * @see Migration_Abstract::restore()
	 */
	public function restore($data)
	{

	}

	/**
	 * @see Migration_Abstract::store()
	 */
	public function store()
	{

	}

	/**
	 * @see Migration_Abstract::up()
	 */
	public function up()
	{
        {if !empty($content)}
            {$content}
        {/if}
		return true;
	}
}