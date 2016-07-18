<?php
/**
 * test_session.php 
 *
 * @copyright  Copyright (c) 2005-2016 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
 require 'client.php';
 
 $client = new SessionClient();
 
 $_SESSION['testdemo'] = array('test1'=>'demo1', 'test2'=>'demo2');
 
 
 var_dump($_SESSION);
 