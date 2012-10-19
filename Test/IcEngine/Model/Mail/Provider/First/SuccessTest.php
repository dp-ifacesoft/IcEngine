<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Mail_Provider_First_Success test case.
 */
class Test_Mail_Provider_First_SuccessTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @desc Номер для проверки
	 * @var string
	 */
	private $_number = '+79134236328';

	/**
	 * @var Mail_Provider_First_Success
	 */
	private $Mail_Provider_First_Success;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();

		$this->Mail_Provider_First_Success = Model_Manager::byQuery (
			'Mail_Provider',
			Query::instance ()
			->where ('name', 'First_Success')
		);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Mail_Provider_First_Success::tearDown()
		$this->Mail_Provider_First_Success = null;
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{
		if (!class_exists ('IcEngine'))
		{
			date_default_timezone_set ('UTC');

			require dirname (__FILE__) . '/../../../../IcEngine.php';
			IcEngine::init ();
			Loader_Auto::register ();

			Loader::addPath ('includes', IcEngine::root() . 'includes/');

			IcEngine::initApplication (
				'Icengine',
				IcEngine::path () . 'Class/Application/Behavior/Icengine.php'
			);
			IcEngine::run ();
		}
	}

	/**
	 * Tests Mail_Provider_First_Success->send()
	 */
	public function testSend ()
	{
		$message = new Mail_Message (array (
			'id'					=> 0,
			'Mail_Template__id'		=> 0,
			'toEmail'				=> $this->_number,
			'toName'				=> 'you',
			'subject'				=> '',
			'body'					=> Helper_Date::toUnix (),
			'time'					=> Helper_Date::toUnix (),
			'sended'				=> 0,
			'sendTime'				=> Helper_Date::toUnix (),
			'sendDay'				=> Helper_Date::eraDayNum (),
			'sendTries'				=> 0,
			'toUserId'				=> 0,
			'Mail_Provider__id'		=> $this->Mail_Provider_First_Success->id,
			'params'				=> ''
		));

		$config = array ();

		$this->_messageId = $this->Mail_Provider_First_Success->send (
			$message,
			$config
		);

		Debug::vardump ('message id: ', $this->_messageId);
	}
}

