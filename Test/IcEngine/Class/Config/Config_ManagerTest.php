<?php

require_once dirname(__FILE__) . '/../../../../../vipgeo.ru/IcEngine/Class/Config/Manager.php';

/**
 * Test class for Config_Manager.
 * Generated by PHPUnit on 2011-07-13 at 05:37:36.
 */
class Config_ManagerTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var Config_Manager
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @todo Implement testEmptyConfig().
	 */
	public function testEmptyConfig() {
		$this->assertEquals (Config_Manager::emptyConfig (), new Config_Array (array ()));
	}

	/**
	 * @todo Implement testGet().
	 */
	public function testGet() {
		
	}

	/**
	 * @todo Implement testGetReal().
	 */
	public function testGetReal() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}

?>
