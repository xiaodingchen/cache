<?php

/**
 * file.php 
 * 
 *
 * @author     late.xiao@qq.com
 */
class SessionFile extends SessionClient {

    public function __construct($options = array())
    {

    }

    public function open($save_path, $session_name)
    {

    }

    public function close()
    {
        $life_time = $this->life_time;
        if(!$life_time)
        {
            $life_time = ini_get('session.gc_maxlifetime');
        }
        
        $this->gc($life_time);
    }

    public function read($session_id)
    {

    }

    public function write($session_id, $session_data)
    {

    }

    public function destroy($session_id)
    {

    }

    public function gc($life_time)
    {

    }

}