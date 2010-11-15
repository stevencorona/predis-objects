<?php

$list = new \Predis\RedisList("key");

$list[] = "foo";   // r->rpush(key, foo)
$list[] = "bar";   // r->rpush(key, bar)
$list[] = "lorem"; // r->rpush(key, lorem)
$list[] = "ipsum"; // r->rpush(key, ipsum)

$list["<<"] = "php5"; // r->lpush(key, php5)

// array(php5, foo, bar, lorem, ipsum)

count($list); // r->llen(key)

// 5

foreach($list as $data) { // r->lrange(key, 0, r->llen(key))
  echo $data;
}

$list->each(2, function($value) { r->lrange(key, 0, 2)
  echo $value;
});

// php5, foo

$list->pop(); // r->lpop(key)
// ipsum
$list->shift(); // r->lpop (key)
// php5


// once you cast the list as an array, you can use all of the normal php array
// functions, but data in the array will no longer be pushed/pulled from redis
$data = (array)$list;
array_values($data);

