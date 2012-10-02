<?php

Loader::load ('Migration_Abstract');

/**
 * @desc {$desc}
 * Created at: {$date}
 * @author {$author}
 * @base {$base}
 * @seq {$seq}
 */
 class Migration_{$name} extends Migration_Abstract
 {
	/**
	 * @see Migration_Abstract::down()
	 */
	public function down ()
	{
		return true;
	}

	/**
	 * @see Migration_Abstract::restore()
	 */
	public function restore ($data)
	{

	}

	/**
	 * @see Migration_Abstract::store()
	 */
	public function store ()
	{

	}

	/**
	 * @see Migration_Abstract::up()
	 */
	public function up ()
	{
		return true;
	}
 }