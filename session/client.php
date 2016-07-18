<?php

/**
 * client.php 
 * 
 *
 * @author     late.xiao@qq.com
 */
class SessionClient {
    protected $handler;
    protected $prefix='session';
    protected $life_time;
    public function __construct($type='',$options = array(), $start=true)
    {
        $this->handler = $this->connect($type, $options);
        $this->life_time = ini_get('session.gc_maxlifetime');
        session_set_save_handler(
        array(&$this->handler, 'open'),
        array(&$this->handler, 'close'),
        array(&$this->handler, 'read'),
        array(&$this->handler, 'write'),
        array(&$this->handler, 'destroy'),
        array(&$this->handler, 'gc')
        );
        
        register_shutdown_function('session_write_close');
        
        if($start)
        {
            session_start();
        }
        
    }
    public function connect($type='',$options = array())
    {
        if(empty($type)) $type = 'file';
        if(file_exists($type.'.php'))
        {
            require $type.'.php';
            $class = 'Session'.ucfirst($type);
            return new $class($options);
        }
        
        throw new Exception("请选择正确的session handler");
        
        return false;
    }

}