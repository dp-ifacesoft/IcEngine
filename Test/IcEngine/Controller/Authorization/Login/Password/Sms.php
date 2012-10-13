<?php
require_once 'Test\Implementation.php';
require_once 'IcEngine\Controller\Authorization\Login\Password\Sms.php';
require_once 'PHPUnit\Framework\TestCase.php';
Test_Implementation::implement ();

/**
 * Controller_Authorization_Login_Password_Sms test case.
 */
class Test_Controller_Authorization_Login_Password_Sms extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Controller_Authorization_Login_Password_Sms
	 */
	private $Controller_Authorization_Login_Password_Sms;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		// TODO Auto-generated Test_Controller_Authorization_Login_Password_Sms::setUp()
		$this->Controller_Authorization_Login_Password_Sms = Controller_Manager::get ('Authorization_Login_Password_Sms');
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Controller_Authorization_Login_Password_Sms::tearDown()
		$this->Controller_Authorization_Login_Password_Sms = null;
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{
		// TODO Auto-generated constructor
	}

	/**
	 * Tests Controller_Authorization_Login_Password_Sms->index()
	 */
	public function testIndex ()
	{
		// TODO Auto-generated Test_Controller_Authorization_Login_Password_Sms->testIndex()
		$this->markTestIncomplete ("index test not implemented");
		$this->Controller_Authorization_Login_Password_Sms->index(/* parameters */);
	}

	/**
	 * Tests Controller_Authorization_Login_Password_Sms->login()
	 */
	public function testLogin ()
	{
		// TODO Auto-generated Test_Controller_Authorization_Login_Password_Sms->testLogin()
		$this->Controller_Authorization_Login_Password_Sms->login ();
	}

	/**
	 * Tests Controller_Authorization_Login_Password_Sms->logout()
	 */
	public function testLogout ()
	{
		// TODO Auto-generated Test_Controller_Authorization_Login_Password_Sms->testLogout()
		$this->markTestIncomplete ("logout test not implemented");
		$this->Controller_Authorization_Login_Password_Sms->logout(/* parameters */);
	}

	/**
	 * Tests Controller_Authorization_Login_Password_Sms->sendSmsCode()
	 */
	public function testSendSmsCode ()
	{
		// TODO Auto-generated Test_Controller_Authorization_Login_Password_Sms->testSendSmsCode()
		$this->markTestIncomplete ("sendSmsCode test not implemented");
		$this->Controller_Authorization_Login_Password_Sms->sendSmsCode(/* parameters */);
	}
}

