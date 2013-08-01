<?php

require_once dirname(__FILE__) . '/../../../../Ice/Model/Captcha.php';

/**
 * Test class for Captcha.
 * Generated by PHPUnit on 2011-07-12 at 07:36:25.
 */
class CaptchaTest extends PHPUnit_Framework_TestCase {


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {

		$locator = IcEngine::serviceLocator();
		$ds = $locator->getService('dataSourceManager')->get('default');
		$dds = $locator->getService('dds');
		$dds->setDataSource($ds);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {

	}

	/**
	 * @todo Implement testAccept().
	 */
	public function testAccept()
	{
		$locator = IcEngine::serviceLocator();
		$captcha = $locator->getService('captcha')->accept();
		$captcha->save();
		$id = $captcha->key();
		$modelManager = $locator->getService('modelManager');
		$captcha_2 = $modelManager->byKey(
			'Captcha',
			$id
		);
		$this->assertEquals($captcha, $captcha_2);
	}

	/**
	 * @todo Implement testCheck().
	 */
	public function testCheck() {
		$locator = IcEngine::serviceLocator();
		$captcha = $locator->getService('captcha');
		$this->assertFalse($captcha->check());
		$_POST['captcha_hash'] = 'fake';
		$this->assertFalse($captcha->check());
		$_SESSION['captcha'] = 'fake';
		$this->assertTrue($captcha->check());
		$this->assertFalse($captcha->check('Fake'));
	}
}