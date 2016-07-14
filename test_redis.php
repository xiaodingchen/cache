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
      * 你也可以使用这样的方式调用
      * $client->get(key);
      * $client->hget(key,field);
      * and so on
      * */
//      $commands = array('set', 'ese');
     
//      $result = $client->executeCommand($commands);
    $result = $client->set('dsw','val','ex',100,'nx');
 } catch (Exception $e) {
     exit($e->getMessage());
 }

 var_dump($result);
 
 