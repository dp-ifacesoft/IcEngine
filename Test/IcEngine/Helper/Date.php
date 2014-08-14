<?php
require_once 'IcEngine\Class\Helper\Date.php';
require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Helper_Date test case.
 */
class Test_Helper_Date extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Helper_Date
	 */
	private $Helper_Date;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		// TODO Auto-generated Test_Helper_Date::setUp()
		$this->Helper_Date = new Helper_Date(/* parameters */);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Helper_Date::tearDown()
		$this->Helper_Date = null;
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{
		date_default_timezone_set ('Asia/Novosibirsk');
	}

	/**
	 * Tests Helper_Date::cmpUnix()
	 */
	public function testCmpUnix ()
	{
		// TODO Auto-generated Test_Helper_Date::testCmpUnix()
		$this->markTestIncomplete ("cmpUnix test not implemented");
		Helper_Date::cmpUnix(/* parameters */);
	}

	/**
	 * Tests Helper_Date::dateByWeek()
	 */
	public function testDateByWeek ()
	{
		// TODO Auto-generated Test_Helper_Date::testDateByWeek()
		$this->markTestIncomplete ("dateByWeek test not implemented");
		Helper_Date::dateByWeek(/* parameters */);
	}

	/**
	 * Tests Helper_Date::eraDayNum()
	 */
	public function testEraDayNum ()
	{
		// TODO Auto-generated Test_Helper_Date::testEraDayNum()
		$this->markTestIncomplete ("eraDayNum test not implemented");
		Helper_Date::eraDayNum(/* parameters */);
	}

	/**
	 * Tests Helper_Date::eraWeekNum()
	 */
	public function testEraWeekNum ()
	{
		// TODO Auto-generated Test_Helper_Date::testEraWeekNum()
		$this->markTestIncomplete ("eraWeekNum test not implemented");
		Helper_Date::eraWeekNum(/* parameters */);
	}

	/**
	 * Tests Helper_Date::getmicrotime()
	 */
	public function testGetmicrotime ()
	{
		// TODO Auto-generated Test_Helper_Date::testGetmicrotime()
		$this->markTestIncomplete ("getmicrotime test not implemented");
		Helper_Date::getmicrotime(/* parameters */);
	}

	/**
	 * Tests Helper_Date::monthEqual()
	 */
	public function testMonthEqual ()
	{
		// TODO Auto-generated Test_Helper_Date::testMonthEqual()
		$this->markTestIncomplete ("monthEqual test not implemented");
		Helper_Date::monthEqual(/* parameters */);
	}

	/**
	 * Tests Helper_Date::monthName()
	 */
	public function testMonthName ()
	{
		// TODO Auto-generated Test_Helper_Date::testMonthName()
		$this->markTestIncomplete ("monthName test not implemented");
		Helper_Date::monthName(/* parameters */);
	}

	/**
	 * Tests Helper_Date::nextTime()
	 */
	public function testNextTime ()
	{
		// TODO Auto-generated Test_Helper_Date::testNextTime()
		$this->markTestIncomplete ("nextTime test not implemented");
		Helper_Date::nextTime(/* parameters */);
	}

	/**
	 * Tests Helper_Date::parseDateTime()
	 */
	public function testParseDateTime ()
	{
		$this->assertEquals (date ('Y-m-d H:i:s'), Helper_Date::toUnix ());
		$test_time = time () + 10050;
		$this->assertEquals (
			date ('Y-m-d H:i:s', $test_time),
			Helper_Date::toUnix ($test_time)
		);
	}

	/**
	 * Tests Helper_Date::secondsBetween()
	 */
	public function testSecondsBetween ()
	{
		// TODO Auto-generated Test_Helper_Date::testSecondsBetween()
		$this->markTestIncomplete (
		"secondsBetween test not implemented");
		Helper_Date::secondsBetween(/* parameters */);
	}

	/**
	 * Tests Helper_Date::strToTimestamp()
	 */
	public function testStrToTimestamp ()
	{
		// TODO Auto-generated Test_Helper_Date::testStrToTimestamp()
		$this->markTestIncomplete (
		"strToTimestamp test not implemented");
		Helper_Date::strToTimestamp(/* parameters */);
	}

	/**
	 * Tests Helper_Date::strToTimeDef()
	 */
	public function testStrToTimeDef ()
	{
		// TODO Auto-generated Test_Helper_Date::testStrToTimeDef()
		$this->markTestIncomplete ("strToTimeDef test not implemented");
		Helper_Date::strToTimeDef(/* parameters */);
	}

	/**
	 * Tests Helper_Date::toCasualDate()
	 */
	public function testToCasualDate ()
	{
		// TODO Auto-generated Test_Helper_Date::testToCasualDate()
		$this->markTestIncomplete ("toCasualDate test not implemented");
		Helper_Date::toCasualDate(/* parameters */);
	}

	/**
	 * Tests Helper_Date::toDateTime()
	 */
	public function testToDateTime ()
	{
		// TODO Auto-generated Test_Helper_Date::testToDateTime()
		$this->markTestIncomplete ("toDateTime test not implemented");
		Helper_Date::toDateTime(/* parameters */);
	}

	/**
	 * Tests Helper_Date::toUnix()
	 */
	public function testToUnix ()
	{
		var_dump (
			Helper_Date::toUnix (time ()),
			Helper_Date::toUnix (time () + 3600)
		);
	}
}

