<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Tiny_Link test case.
 */
class Test_Tiny_Link extends PHPUnit_Framework_TestCase
{

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{

	}

	public function testIntDecode ()
	{
		$max = 2000000000;
		$random_step = (int) ($max / 6);
		
		$i = 1;
		while ($i < $max)
		{
			$d = Tiny_Link::intDecode ($i);
			$this->assertEquals ($i, Tiny_Link::intEncode ($d));
			$i += rand(1, $random_step);
		}
	}
	
	public function testIntEncode ()
	{
		$max = 2000000000;
		$random_step = (int) ($max / 6);
		
		$i = 1;
		while ($i < $max)
		{
			$d = Tiny_Link::intEncode ($i);
			$this->assertEquals ($i, Tiny_Link::intDecode ($d));
			$i += rand(1, $random_step);
		}
	}
	
}

