<?php

$array = array();

$array[] = "foo";
$array[4] = "bar";

array_unshift($array, "test");
print_r($array);

class Test {

	static $list = array("test");
	
	public function __construct() {
		init_redis_obj($this);
	}
}


function init_redis_obj($klass) {
	foreach($klass::$list as $key) {
		$klass->$key = new RList($key);
	}
}