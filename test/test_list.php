<?php

require_once dirname(__FILE__) . "/../list.php";

class test_list extends PHPUnit_Framework_TestCase {

	public function tearDown() {
		$this->redis   = new Predis\Client('redis://localhost:6379/');
		$this->redis->del("test_key");
	}

	public function test_can_create_list() {
		$list = new RList("test_key");
		$this->assertTrue($list instanceOf RList);
	}
	
	public function test_simple_set_get() {
		$list = new RList("test_key");
		$list[] = "steve";
		$list[] = "bob";
		
		$this->assertEquals($list[0]);
		$this->assertEquals("bob", $list[1]);
	}
	
	public function test_complex_set_get() {
		$list = new RList("test_key");
		$list[] = "john";
		$list[] = "herald";
		$list[] = "kumar";
		$list["<<"] = "steve";
		
		$list = new RList("test_key");
		
		$this->assertEquals("steve", $list[0]);
		$this->assertEquals("kumar", $list[3]);
		$this->assertEquals(4, count($list));
	}
	
	public function test_count() {
		$list = new RList("test_key");
		foreach(range(1,100) as $index) { $list[] = $index; }
		$this->assertEquals(100, count($list));
	}
	
	public function test_isset() {
		$list = new RList("test_key");
		$list[] = "steve";
		$list[] = "john";
		
		$this->assertTrue(isset($list[0]));
		$this->assertFalse(isset($list[10]));
	}
	
	public function test_data_persists() {
		$list = new RList("test_key");
		$list[] = "john";
		$list[] = "herald";
		$list[] = "kumar";
		$list["<<"] = "steve";
		
		unset($list);
		
		$list = new RList("test_key");
		$this->assertEquals("steve", $list[0]);
		$this->assertEquals("kumar", $list[3]);
		$this->assertEquals(4, count($list));
	}
	
	public function test_loop() {
		$list = new RList("test_key");
		$list[] = "john";
		$list[] = "herald";
		$list[] = "kumar";
		$list["<<"] = "steve";
		
		$names = array("steve", "john", "herald", "kumar");

		$loop_iterations = 0;
		foreach($list as $index => $name) {
			$this->assertEquals($names[$index], $name);
			$loop_iterations++;
		}
		
		$this->assertEquals(4, $loop_iterations);
		
	}
	
	public function test_loop_range() {
		
		$list = new RList("test_key");
		$list[] = "john";
		$list[] = "herald";
		$list[] = "kumar";
		$list["<<"] = "steve";
		
		$names = array("steve", "john");

		$loop_iterations = 0;
		
		$that = $this;
		$list->each(2, function($index, $value) use($that, $names, $loop_iterations) {
			$that->assertTrue(isset($names[$index]));
			$that->assertEquals($names[$index], $value);
		});		
		
	}
	
	public function test_unset() {
		
		$list = new RList("test_key");
		$list[] = "john";
		$list[] = "herald";
		$list[] = "kumar";
		$list["<<"] = "steve";
		
		unset($list[0]);
		
		$this->assertEquals(3, count($list));
		$this->assertEquals("john", $list[0]);
		
	}
	
	public function test_pop() {
		$list = new RList("test_key");
		$list[] = "john";
		$list[] = "herald";
		$list[] = "kumar";
		$list["<<"] = "steve";

		$this->assertEquals("kumar", $list->pop());
		$this->assertEquals(3, count($list));
		
	}
	
	public function test_shift() {
		$list = new RList("test_key");
		$list[] = "john";
		$list[] = "herald";
		$list[] = "kumar";
		$list["<<"] = "steve";

		$this->assertEquals("steve", $list->shift());
		$this->assertEquals(3, count($list));
		
	}
	
	public function test_expirations() {
		
		$list = new RList("test_key");
		
		$list[] = "data";
		
		$this->assertEquals(-1, $list->ttl());
		$list->expire(60);
		$this->assertEquals(60, $list->ttl());
		$list->persist();
		$this->assertEquals(-1, $list->ttl());
		
	}
	
	public function test_expirations_constructor() {
		
		$list = new RList("test_key", 60);
		$list[] = "data";
		
		$this->assertEquals(60, $list->ttl());
		$list->expire(100);
		$this->assertEquals(100, $list->ttl());
		$list->persist();
		$this->assertEquals(-1, $list->ttl());
		
	}
	
}