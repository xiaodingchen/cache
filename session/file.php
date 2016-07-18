<?php

/**
 * file.php 
 * 
 *
 * @author     late.xiao@qq.com
 */
class SessionFile extends SessionClient {
    protected $save_path;

    public function __construct($options = array())
    {

        $this->parseOptions($options);
    }

    public function open($save_path, $session_name)
    {
        if(!$this->save_path)
        {
            $this->save_path = $save_path;
        }
        if (! is_dir($this->save_path))
        {
            mkdir($this->save_path, 0777);
        }
        
        return true;
    }

    public function close()
    {

        $this->gc($this->life_time);
        
        return true;
    }

    public function read($session_id)
    {

        return (string) @file_get_contents($this->save_path . '/' . $this->prefix . '_' . $session_id);
    }

    public function write($session_id, $session_data)
    {

        $file_name = $this->save_path . '/' . $this->prefix . '_' . $session_id;
        
        return file_put_contents($file_name, $session_data) === false ? false : true;
    }

    public function destroy($session_id)
    {

        $file_name = $this->save_path . '/' . $this->prefix . '_' . $session_id;
        if (file_exists($file_name))
        {
            unlink($file_name);
        }
        
        return true;
    }

    public function gc($life_time)
    {

        $tmp_arr = glob("{$this->save_path}/{$this->prefix}_*");
        foreach ($tmp_arr as $file)
        {
            if (filemtime($file) + $this->life_time < time() && file_exists($file))
            {
                unlink($file);
            }
        }
        
        return true;
    }
    
    // 解析参数
    protected function parseOptions($options)
    {

        if (isset($options ['save_path']))
        {
            $this->save_path = $options ['save_path'];
        }
        
        if (isset($options ['prefix']))
        {
            $this->prefix = $options ['prefix'];
        }
        
        if (isset($options ['life_time']))
        {
            $this->life_time = $options ['life_time'];
        }
        
        return true;
    }

}
