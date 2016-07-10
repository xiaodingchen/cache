<?php
/**
 * test_redis.php 
 * 
 *
 * @author     late.xiao@qq.com
 */
 error_reporting(0);
 require_once 'redis.php';
 
 try {
     $client = new RedisClient('tcp://127.0.0.1:6379');
     /**
      * $commands = array('set','key','value');
      * $commands = array('get','key');
      * 
      * */
     $commands = array('ping');
     
     $result = $client->executeCommand($commands);
 } catch (Exception $e) {
     exit($e->getMessage());
 }
 
 var_dump($result);
 
 