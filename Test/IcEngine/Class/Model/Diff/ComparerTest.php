<?php

define('ICENGINE_DIR',"/var/www/forguest.me/");

require_once ICENGINE_DIR.'IcEngine/Class/Helper/Diff/Comparer.php';
require_once ICENGINE_DIR.'Ice/Model/Flat.php';
require_once ICENGINE_DIR.'Ice/Model/Room.php';
require_once ICENGINE_DIR.'Ice/Model/Room/Collection.php';


class Test_Model_Diff extends PHPUnit_Framework_TestCase
{
	protected $origFlat;
	
	protected function getComparerResult($flat)
	{
		$this->comparer = new Helper_Diff_Comparer($this->origFlat, $flat);
		return $this->comparer->compare();
	}
			
	protected function setUp()
	{
		$_SERVER['HTTP_HOST'] = 'www.forguest.me';
		DDS::setDataSource ( Data_Source_Manager::get ('www.forguest.ru') );		
		Loader::load ('Model_Scheme');
		
		Model_Scheme::init ( Config_Manager::get ('Model_Scheme', 'Forguest') );
		
		$this->origFlat = new Flat(array(
					'id' => 1,
					'city_id' => 1,
					'address' => 'addr',
					'metros' => array(1,2,3),
					'rooms' => array(7,8,9) 
		));
		$this->origFlat->rooms = Model_Collection_Manager::byQuery('Room', Query::instance()
									->where('id',$this->origFlat->rooms)
								);
		
		DDS::execute(
			Query::instance()
				->delete()
				->from("Edit")
		);
		DDS::execute(
			Query::instance()
				->delete()
				->from("Edit_Field")
		);
		DDS::execute(
			Query::instance()
				->delete()
				->from("Edit_Value")
		);
				
	}
	
	
	function testDifferentClasses()
	{
		$this->assertEmpty( $this->getComparerResult(new Objective) );
	}
	
	function testEqualModels()
	{
		$this->assertTrue( $this->getComparerResult($this->origFlat) );
	}
	
	function testCompareValueTypes()
	{
		$flat = clone $this->origFlat;
		$flat->address = 'addr2';
		$result = $this->getComparerResult($flat);
		$this->assertTrue(is_array($result) && count($result)==1);
		$this->assertEquals($result[0]["name"], 'address');
		$this->assertEquals($result[0]["value"], 'addr2');
	}
	
	function testCompareForeignKeys()
	{
		$flat = clone $this->origFlat;
		$flat->city_id = 2;
		$result = $this->getComparerResult($flat);
		$this->assertTrue(is_array($result) && count($result)==1);
		$this->assertEquals($result[0]["name"], 'city_id');
		$this->assertEquals($result[0]["value"], '2');
	}

	function testCompareManyToManyArrayIds()
	{
		$flat = clone $this->origFlat;
		$flat->metros = array(12,13,14);
		$result = $this->getComparerResult($flat);
		$this->assertTrue(is_array($result) && count($result)==1);
		$this->assertEquals($result[0]["name"], 'metros');
		$this->assertTrue(  count(array_diff($result[0]["value"], array(12,13,14) ))==0 );
	}

	function testCompareOneToManyModelCollections()
	{
		$flat = clone $this->origFlat;
		$flat->rooms = Model_Collection_Manager::byQuery('Room', Query::instance()
									->where('id', array(8,9,10) )
								)->load();
		$flat->rooms->filter(array( 'id' => 9 ))->first()->name='test';
		$result = $this->getComparerResult($flat);
		$this->assertTrue(is_array($result) && count($result)==1);
		$this->assertEquals($result[0]["name"], 'rooms');
	}

	
	function testAll()
	{
		$flat = clone $this->origFlat;
		$flat->address = 'addr2';
		$flat->city_id = 2;
		$flat->metros = array(12,13,14);
		$flat->rooms = Model_Collection_Manager::byQuery('Room', Query::instance()
									->where('id', array(8,9,10) )
								)->load();
		$flat->rooms->filter(array( 'id' => 9 ))->first()->name='test';
		$result = $this->getComparerResult($flat);
		$this->assertTrue(is_array($result) && count($result)==4);
	}	
	
}