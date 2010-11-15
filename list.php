<?php
namespace Predis;

error_reporting(E_ALL);
require_once(dirname(__FILE__)."/predis.php");


// TODO
// * Option to serialize sets, unserialize gets. Will require custom Iterator
// * Option to load an existing php array
// * Option to use pipelining
// * Inheret from a base RedisArrayObject

class RedisList extends \ArrayObject {

  static $LPUSH_KEYS = array("lpush", "<<", "l");

  public $redis_key;

  public function __construct($redis_key, $expiration=null) {
    $this->redis_key = $redis_key;
    $this->redis   = new Client('redis://localhost:6379/');

    if ($expiration !== null) {
      $this->expire($expiration);
    }

  }

  public function expire($expiration) {
    return $this->redis->expire($this->redis_key, $expiration);
  }

  public function ttl() {
    return $this->redis->ttl($this->redis_key);
  }

  public function persist() {
    return $this->expire(0);
  }

  public function rename($new_redis_key, $play_nice=true) {
    if ($play_nice === true) {
      $this->redis->renamenx($this->redis_key, $new_redis_key);
    } else {
      $this->redis->rename($this->redis_key, $new_redis_key);
    }

    $this->redis_key = $new_redis_key;
  }

  public function getIterator($start=0, $end=null){

    if ($end === null) {
      $end = $this->count();
    }
    $range = $this->redis->lrange($this->redis_key, $start, --$end);
    return new \ArrayIterator($range);
  }

  public function each($opts, $block) {
    $default_opts = array("start" => 0, "end" => 25);

    if (! is_array($opts)) {
      $opts = array("end" => $opts);
    }

    $opts = array_merge($default_opts, $opts);

    $iterator = $this->getIterator($opts["start"], $opts["end"]);
    while ($iterator->valid()) {
      $block($iterator->key(), $iterator->current());
      $iterator->next();
    }
  }

  public function count() {
    return $this->redis->llen($this->redis_key);
  }

  public function offsetExists($index) {
    return parent::offsetExists($index);
  }

  public function offsetGet($index) {
    return $this->redis->lindex($this->redis_key, $index);
  }

  public function offsetSet($index, $value) {

    if (!is_numeric($index) && $index !== null && ! in_array($index, self::$LPUSH_KEYS)) throw new Exception("key must be integer");

    if ($index === null) {
      $this->redis->rpush($this->redis_key, $value);
    } else {

      if (in_array($index, self::$LPUSH_KEYS)) {
        $this->redis->lpush($this->redis_key, $value);
        $index = null;
      } else {
        $this->redis->lset($this->redis_key, $index, $value);
      }

    }

    return parent::offsetSet($index, $value);

  }

  public function offsetUnset($index) {

    $uuid = uniqid();
    $this->redis->lset($this->redis_key, $index, $uuid);
    $this->redis->lrem($this->redis_key, 1, $uuid);
    return parent::offsetUnset($index);
  }

  public function pop() {
    return $this->redis->rpop($this->redis_key);
  }

  public function shift() {
    return $this->redis->lpop($this->redis_key);
  }

}