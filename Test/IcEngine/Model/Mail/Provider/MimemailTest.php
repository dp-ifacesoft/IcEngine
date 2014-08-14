<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Mail_Provider_Mimemail test case.
 */
class Test_Mail_Provider_MimemailTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Mail_Provider_Mimemail
	 */
	private $Mail_Provider_Mimemail;
	
	/**
	 * @desc Тестовый ящик
	 * @var string
	 */
	private $_email = 'goorus@list.ru';
	
	/**
	 * @desc Кому
	 * @var string
	 */
	private $_toName = 'goorus';
	
	/**
	 * @desc Тело письма
	 * @return string
	 */
	protected function _body ()
	{
		return 
			'IcEngine.' . get_class ($this) . ': ' .
			Helper_Date::toUnix ();
	}

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		// TODO Auto-generated Test_Mail_Provider_Mimemail::setUp()
		$this->Mail_Provider_Mimemail = Model_Manager::byQuery (
			'Mail_Provider',
			Query::instance ()
			->where ('name', 'Mimemail')
		);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Mail_Provider_Mimemail::tearDown()
		$this->Mail_Provider_Mimemail = null;
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{

	}

	/**
	 * Tests Mail_Provider_Mimemail->send()
	 */
	public function testSend ()
	{
		$message = new Mail_Message (array (
			'id'					=> 0,
			'Mail_Template__id'		=> 0,
			'toEmail'				=> $this->_email,
			'toName'				=> $this->_toName,
			'subject'				=> 'subj: ' . Helper_Date::toUnix (),
			'body'					=> $this->_body (),
			'time'					=> Helper_Date::toUnix (),
			'sended'				=> 0,
			'sendTime'				=> Helper_Date::toUnix (),
			'sendDay'				=> Helper_Date::eraDayNum (),
			'sendTries'				=> 0,
			'toUserId'				=> 0,
			'Mail_Provider__id'		=> $this->Mail_Provider_Mimemail->id,
			'params'				=> ''
		));
			
		$r = $message->save ()->send ();
		
		$this->assertFalse (!$r, 'Not sended');
	}

	/**
	 * Tests Mail_Provider_Mimemail->sendEx()
	 */
	public function testSendEx ()
	{
		$r = $this->Mail_Provider_Mimemail->sendEx (
			$this->_email,
			'subject',
			'body',
			array ()
		);
		
		$this->assertFalse (!$r, 'Not sended');
	}
	
	/**
	 * Tests Mail_Provider_Mimemail->send()
	 */
	public function testSendRus ()
	{
		$message = new Mail_Message (array (
			'id'					=> 0,
			'Mail_Template__id'		=> 0,
			'toEmail'				=> $this->_email,
			'toName'				=> $this->_toName,
			'subject'				=> 
				'Русские буквы в заголовке: ' .
				Helper_Date::toUnix (),
			'body'					=> 
				'Русские буквы в теле сообщения.',
			'time'					=> Helper_Date::toUnix (),
			'sended'				=> 0,
			'sendTime'				=> Helper_Date::toUnix (),
			'sendDay'				=> Helper_Date::eraDayNum (),
			'sendTries'				=> 0,
			'toUserId'				=> 0,
			'Mail_Provider__id'		=> $this->Mail_Provider_Mimemail->id,
			'params'				=> ''
		));
			
		$r = $message->save ()->send ();
		
		$this->assertFalse (!$r, 'Not sended');
	}
	
}

