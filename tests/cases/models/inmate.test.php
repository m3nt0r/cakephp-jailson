<?php
App::import('Model','Jailson.Inmate'); 

class TestInmate extends Inmate {
	public $useTable = 'test_inmates';
	public $useDbConfig = "test_suite";
	public $cacheSources = false;
	public $hasAndBelongsToMany = array();
	public $belongsTo = array();
	public $hasOne = array();
	public $hasMany = array();
}

class InmateTestCase extends CakeTestCase { 
	public $fixtures = array('plugin.jailson.inmate');
	public $Inmate;
	
	public function startTest() {
		$this->Inmate = new TestInmate();
	}
	public function endTest() {
		unset($this->Inmate);
	}
	public function testInstance() {
		$this->assertTrue(is_a($this->Inmate, 'Inmate'));
	}
	
	# =================================================
	# Inmate::drilldown()
	# =================================================
	
	public function testDrilldown() {
	
		// wide
		$result = $this->Inmate->drilldown('Testuser');
		$expected = array( 
			'Testuser/1/singer', 
			'Testuser/1/singer/pianist',
			'Testuser/2/bass', 
			'Testuser/2/bass/guitar',
			'Testuser/3/lead',
			'Testuser/3/lead/guitar',
			'Testuser/4/drummer',
			'Testuser/4/drummer/percussions',
		);
		$this->assertEqual($result, $expected);

		// deep
		$result = $this->Inmate->drilldown('Testuser/2/b');
		$expected = array( 
			'Testuser/2/bass',
			'Testuser/2/bass/guitar'
		);
		$this->assertEqual($result, $expected);

		// not found
		$result = $this->Inmate->drilldown('idontexist');
		$expected = array( 
		);
		$this->assertEqual($result, $expected);

		// empty key
		$result = $this->Inmate->drilldown('');
		$expected = array( 
		);
		$this->assertEqual($result, $expected);
	}

	# =================================================
	# Inmate::retrieve()
	# =================================================
	
	public function testRetrieve() {
	
		// string query
		$result = $this->Inmate->retrieve('Testuser/1/singer');
		$expected = array( 
			'Testuser/1/singer'
		);
		$this->assertEqual($result, $expected);

		// array query
		$result = $this->Inmate->retrieve(array('Testuser/1/singer'));
		$expected = array( 
			'Testuser/1/singer'
		);
		$this->assertEqual($result, $expected);

		// empty query
		$result = $this->Inmate->retrieve('');
	    $expected = array( 
		);
		$this->assertEqual($result, $expected);

		// not found
		$result = $this->Inmate->retrieve(array('Testuser/1/drummer'));
	    $expected = array( 
		);
		$this->assertEqual($result, $expected);
	}

	# =================================================
	# Inmate::store()
	# =================================================

	public function testStore() {
		
		// store string
		$result = $this->Inmate->store('Testuser/1/pianist');
		$expected = array( 
			'Testuser/1/pianist'
		);
		$this->assertEqual($result, $expected);

		// remove string
		$result = $this->Inmate->remove('Testuser/1/pianist');
		$expected = array( 
			'Testuser/1/pianist'
		);
		$this->assertEqual($result, $expected);

		// store array, multiple entries
		$result = $this->Inmate->store(array(
			'Testuser/1/testing',
			'Testuser/1/makes',
			'Testuser/1/perfect',
		));
		$expected = array( 
			'Testuser/1/testing',
			'Testuser/1/makes',
			'Testuser/1/perfect',
		);
		$this->assertEqual($result, $expected);

		// is in storage?
		$result = $this->Inmate->retrieve('Testuser/1/perfect');
		$expected = array( 
			'Testuser/1/perfect'
		);
		$this->assertEqual($result, $expected);

		// store existing, shouldn't do anything
		$result = $this->Inmate->store('Testuser/1/singer');
		$expected = array( 
		);
		$this->assertEqual($result, $expected);
		
	}
	
	# =================================================
	# Inmate::remove()
	# =================================================
	
	public function testRemove() {
		
		// remove string
		$result = $this->Inmate->remove('Testuser/1/singer');
		$expected = array( 
			'Testuser/1/singer'
		);
		$this->assertEqual($result, $expected);
		
		// remove array
		$result = $this->Inmate->remove(array('Testuser/3/lead', 'Testuser/4/drummer'));
		$expected = array(
			'Testuser/3/lead', 
			'Testuser/4/drummer'
		);
		$this->assertEqual($result, $expected);

		// remove existing and non existing
		$result = $this->Inmate->remove(array('Testuser/2/bass', 'idontexist'));
		$expected = array(
			'Testuser/2/bass'
		);
		$this->assertEqual($result, $expected);
	}
	
	public function testRemoveBadArgs() {
		// remove empty
		$result = $this->Inmate->remove('');
		$expected = array( 
		);
		$this->assertEqual($result, $expected);
		
		// remove not found
		$result = $this->Inmate->remove('Testuser/1/pianist');
		$expected = array( 
		);
		$this->assertEqual($result, $expected);
	}
	
	# =================================================
	# Inmate::removeTree()
	# =================================================

	public function testRemoveTree() {
		
		// remove tree (2 level)
		$result = $this->Inmate->removeTree('Testuser/1/singer');
		$expected = array( 
			'Testuser/1/singer', 
			'Testuser/1/singer/pianist',
		);
		$this->assertEqual($result, $expected);

		// remove string (3 level)
		$result = $this->Inmate->removeTree('Testuser/2');
		$expected = array( 
			'Testuser/2/bass', 
			'Testuser/2/bass/guitar',
		);
		$this->assertEqual($result, $expected);

		$result = $this->Inmate->removeTree('Testuser');
		$expected = array( 
			'Testuser/3/lead',
			'Testuser/3/lead/guitar',
			'Testuser/4/drummer',
			'Testuser/4/drummer/percussions',
		);
		$this->assertEqual($result, $expected);

	}
}
?>