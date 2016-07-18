<?php

/**
 * test_session.php 
 * 
 *
 * @author     late.xiao@qq.com
 */
 
 require 'client.php';
 
 $client = new SessionClient();
 
 $_SESSION['testdemo'] = array('test1'=>'demo1', 'test2'=>'demo2');
 
 
 var_dump($_SESSION);
 