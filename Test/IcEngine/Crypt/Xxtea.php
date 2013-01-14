<?php
require_once 'IcEngine\Model\Crypt\Abstract.php';
require_once 'IcEngine\Model\Crypt\Xxtea.php';
require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Crypt_Xxtea test case.
 */
class Test_Crypt_Xxtea extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Crypt_Xxtea
	 */
	private $Crypt_Xxtea;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		// TODO Auto-generated Test_Crypt_Xxtea::setUp()
		$this->Crypt_Xxtea = new Crypt_Xxtea(/* parameters */);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Crypt_Xxtea::tearDown()
		$this->Crypt_Xxtea = null;
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
	 * Tests Crypt_Xxtea->decode()
	 */
	public function testDecode ()
	{
		// TODO Auto-generated Test_Crypt_Xxtea->testDecode()
		$this->markTestIncomplete ("decode test not implemented");
		$this->Crypt_Xxtea->decode(/* parameters */);
	}

	/**
	 * Tests Crypt_Xxtea->encode()
	 */
	public function testEncode ()
	{
		// TODO Auto-generated Test_Crypt_Xxtea->testEncode()
		$this->markTestIncomplete ("encode test not implemented");
		$this->Crypt_Xxtea->encode(/* parameters */);
	}
	
	public function testRight ()
	{
		$tests = array (
			'asdjfkljdslfijasdlifjalsij liasdfa',
			'0',
			'',
			'JFiljasjdf 9jaljljdfjal4jofjlajaflds',
			'дваод фышоДОВОА ОЫВао офдшоа фывоаш фыво афод',
			'1234567890-!@#$%^&*()_'
		);
		
		$keys = array (
			'',
			'123456789',
			'0',
			'jldjf asd fj8jJF sdfsdjfв фыва фыва фывадфышдшодшодОДОДШО'
		);
		
		foreach ($tests as $test)
		{
			foreach ($keys as $key)
			{
				$crypted = $this->Crypt_Xxtea->encode ($test, $key);
				$uncrypted = $this->Crypt_Xxtea->decode ($crypted, $key);
				$this->assertEquals ($test, $uncrypted);
			}
		}
	}
}

