<?php
require_once 'IcEngine\Class\Paginator.php';
require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Paginator test case.
 */
class Test_Paginator extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Paginator
	 */
	private $Paginator;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		// TODO Auto-generated Test_$this->getService('paginator')->setUp()
		$this->Paginator = new Paginator(/* parameters */);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_$this->getService('paginator')->tearDown()
		$this->Paginator = null;
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
	 * Tests Paginator->__construct()
	 */
	public function test__construct ()
	{
		// TODO Auto-generated Test_Paginator->test__construct()
		$this->markTestIncomplete ("__construct test not implemented");
		$this->Paginator->__construct(/* parameters */);
	}
	
	public function testRegexp ()
	{
		$href = '/search/?query=Агентство';
		$p = 'page';
		$href = preg_replace (
			"/((?:\?|&)$p(?:\=[^&]*)?$)+|((?<=[?&])$p(?:\=[^&]*)?&)+|((?<=[?&])$p(?:\=[^&]*)?(?=&|$))+|(\?$p(?:\=[^&]*)?(?=(&$p(?:\=[^&]*)?)+))+/", 
			'', 
			$href
		);
		
		var_dump ($href);
	}

	/**
	 * Tests Paginator->buildPages()
	 */
	public function testBuildPages ()
	{
		// TODO Auto-generated Test_Paginator->testBuildPages()
		$this->markTestIncomplete ("buildPages test not implemented");
		$this->Paginator->buildPages(/* parameters */);
	}

	/**
	 * Tests $this->getService('paginator')->fromGet()
	 */
	public function testFromGet ()
	{
		// TODO Auto-generated Test_$this->getService('paginator')->testFromGet()
		$this->markTestIncomplete ("fromGet test not implemented");
		$this->getService('paginator')->fromGet(/* parameters */);
	}

	/**
	 * Tests $this->getService('paginator')->fromInput()
	 */
	public function testFromInput ()
	{
		// TODO Auto-generated Test_$this->getService('paginator')->testFromInput()
		$this->markTestIncomplete ("fromInput test not implemented");
		$this->getService('paginator')->fromInput(/* parameters */);
	}

	/**
	 * Tests Paginator->offset()
	 */
	public function testOffset ()
	{
		// TODO Auto-generated Test_Paginator->testOffset()
		$this->markTestIncomplete ("offset test not implemented");
		$this->Paginator->offset(/* parameters */);
	}

	/**
	 * Tests Paginator->pagesCount()
	 */
	public function testPagesCount ()
	{
		// TODO Auto-generated Test_Paginator->testPagesCount()
		$this->markTestIncomplete ("pagesCount test not implemented");
		$this->Paginator->pagesCount(/* parameters */);
	}
}

