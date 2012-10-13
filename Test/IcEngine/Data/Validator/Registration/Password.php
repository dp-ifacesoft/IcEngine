<?php
require_once 'IcEngine\Test\Implementation.php';
require_once 'IcEngine\Class\Data\Validator\Registration\Password.php';
require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Data_Validator_Registration_Password test case.
 */
class Test_Data_Validator_Registration_Password extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Data_Validator_Registration_Password
	 */
	private $Data_Validator_Registration_Password;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		// TODO Auto-generated Test_Data_Validator_Registration_Password::setUp()
		$this->Data_Validator_Registration_Password = new Data_Validator_Registration_Password(/* parameters */);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Data_Validator_Registration_Password::tearDown()
		$this->Data_Validator_Registration_Password = null;
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
	 * Tests Data_Validator_Registration_Password->validate()
	 */
	public function testValidate ()
	{
		// TODO Auto-generated Test_Data_Validator_Registration_Password->testValidate()
		$tests = array (
			'1'										=> false,
			'fuck'									=> false,
			'password'								=> true,
			'gj9834uejt98w53i'						=> true,
			'12345678901234567890'					=> true,
			'CoolToHate.WelcomeToAmericana'			=> false,
			'!@#$%^&*()'							=> null
		);
		
		$results = array ();
		foreach ($tests as $test => $result)
		{
			$valid = $this->Data_Validator_Registration_Password->validate ($test);
			if (is_null ($result))
			{
				$results [$test] = $valid;
			}
			else
			{
				$this->assertEquals ($result, $valid);
			}
		}
		
		var_dump ($results);
	}

	/**
	 * Tests Data_Validator_Registration_Password->validateEx()
	 */
	public function testValidateEx ()
	{
		// TODO Auto-generated Test_Data_Validator_Registration_Password->testValidateEx()
		$this->markTestIncomplete ("validateEx test not implemented");
		$this->Data_Validator_Registration_Password->validateEx(/* parameters */);
	}
}

