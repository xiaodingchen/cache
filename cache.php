<?php

/**
 * cache.php 
 * 
 *
 * @author     late.xiao@qq.com
 */
class cache {
    private static $_instance;
    private $obj;
    public function __construct($type)
    {

        include 'driver/' . $type;
        $config = require 'config.php';
        $config = $config[$type];
        $this->obj = new $type;
        $this->obj->init($config);
    }

    static public function getinstance($type = 'file')
    {
        static $_instance;
        if(!self::$_instance instanceof self)
        {
            self::$_instance = new self($type);
        }
        
        return self::$_instance;
    }
    
    

}
 
 