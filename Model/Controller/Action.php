<?php

/**
 * Действие контроллера
 *
 * @author goorus, morph
 */
class Controller_Action extends Objective
{
	/**
	 * Флаг игнорирования
	 */
	const IGNORE_FLAG = 'ignore';

	/**
	 * Транспорт входной
     *
	 * @var Data_Transport
	 */
	public $input;

	/**
	 * Транспорт выходной
     *
	 * @var Data_Transport
	 */
	public $output;

}