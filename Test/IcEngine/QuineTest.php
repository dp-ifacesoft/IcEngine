<?php

require_once dirname(__FILE__) . '/../../Quine.php';

/**
 * Test class for Quine.
 * Generated by PHPUnit on 2011-09-09 at 07:53:00.
 */
class QuineTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var Quine
	 */
	protected $object;
	
	private $selenium;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->object = new Quine;
		$this->selenium = new Testing_Selenium("*iexplore", "http://www.google.ru");
		$this->selenium->start();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		$this->selenium->stop();
	}

	/**
	 * @todo Implement testGet().
	 */
	public function testGet() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}
	
	/**
	 * @todo Implement testGoogle().
	 */
    public function testGoogle()
	{
		$this->selenium->open("/");
		$this->selenium->type("q", "hello world");
		$this->selenium->click("btnG");
		$this->selenium->waitForPageToLoad(10000);
		// русский текст в кодировке UTF-8 !
		$this->assertRegExp("/Поиск в Google/", $this->selenium->getTitle());
	}

}
