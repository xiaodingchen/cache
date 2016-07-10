<?php
/**
 * test_redis.php 
 * 
 *
 * @author     late.xiao@qq.com
 */
 
 require_once 'redis.php';
 
 $client = new RedisClient('tcp://127.0.0.1:6379');
 /**
  * 
  * $commands = array('set','keyname','value');
  * $commands = array('get','keyname');
  * 
  * */
 $commands = array('ping');
 
 
 $result = $client->executeCommand($commands);
 
 var_dump($result);
 exit;