<?php
require_once 'IcEngine\Test\Implementation.php';
require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Helper_Activation test case.
 */
class Test_Helper_Activation extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Helper_Activation
	 */
	private $Helper_Activation;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		// TODO Auto-generated Test_Helper_Activation::setUp()
		$this->Helper_Activation = new Helper_Activation(/* parameters */);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Helper_Activation::tearDown()
		$this->Helper_Activation = null;
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{
		// TODO Auto-generated constructor
		Test_Implementation::implement ();
	}

	/**
	 * Tests Helper_Activation::generateNumeric()
	 */
	public function testGenerateNumeric ()
	{
		$codes = array ();
		$min_length = 999;
		$max_length = 0;
		
		$start_time = microtime (true);
		for ($i = 0; $i < 100; ++$i)
		{
			$code = Helper_Activation::generateNumeric ();
			$min_length = min ($min_length, strlen ($code));
			$max_length = max ($max_length, strlen ($code));
			$codes [] = $code;
		}
		$delta_time = microtime (true) - $start_time;
		
		var_dump (array (
			'time'			=> $delta_time,
			'min_length'	=> $min_length,
			'max_length'	=> $max_length
		));
		var_dump ($codes);
	}

	/**
	 * Tests Helper_Activation::newShortCode()
	 */
	public function testNewShortCode ()
	{
		// TODO Auto-generated Test_Helper_Activation::testNewShortCode()
		$this->markTestIncomplete ("newShortCode test not implemented");
		Helper_Activation::newShortCode(/* parameters */);
	}

	/**
	 * Tests Helper_Activation::byShortCode()
	 */
	public function testByShortCode ()
	{
		// TODO Auto-generated Test_Helper_Activation::testByShortCode()
		$this->markTestIncomplete ("byShortCode test not implemented");
		Helper_Activation::byShortCode(/* parameters */);
	}
}

