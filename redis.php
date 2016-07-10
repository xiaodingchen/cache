<?php

/**
 * redis.php 
 * 以下代码主要借鉴了predis，如有雷同纯属抄袭
 *
 * @author     late.xiao@qq.com
 */
class RedisClient {
    public $remote;
    public $resource;
    public $initCommands;
    const TIME_OUT = 5.0;

    /**
     * 初始化一个redis资源
     * @param string $remote 一个redis tcp uri 比如：tcp://127.0.0.1
     * @param array $initCommands redis初始化命令，比如认证命令:array('auth','passwd')
     * */
    public function __construct($remote, array $initCommands=array())
    {

        $this->remote = $remote;
        $this->initCommands = $initCommands;
        //$this->connect();
    }
    
    // 连接资源
    public function connect()
    {
        if(!$this->isConnected())
        {
            $this->resource = $this->createResource();
            if($this->initCommands)
            {
                $this->executeCommand($this->initCommands);
            }
            return true;
        }
        
        return false;
    }
    
    // 获取一个资源
    public function getResource()
    {
        if(isset($this->resource))
        {
            return $this->resource;
        }
        
        $this->connect();
        
        return $this->resource;
    }
    
    // 判断资源是否存在
    public function isConnected()
    {
        return isset($this->resource);
    }

    public function createResource()
    {
        $errno=$errstr=null;
        $scoket = stream_socket_client($this->remote, $errno, $errstr, (float) self::TIME_OUT, STREAM_CLIENT_CONNECT);
        if($errno)
        {
            throw new Exception("{$errno}:{$errstr}");
        }
        stream_set_timeout($scoket, 100);
        
//         $this->resource = $scoket;
        return $scoket;
    }
    
    // 执行命令返回结果
    public function executeCommand(array $arguments)
    {

        $this->writeRequest($arguments);
        return $this->readResponse($arguments);
    }
    
    // 读取命令执行后的结果
    public function readResponse(array $arguments)
    {

        return $this->read();
    }
    
    // 处理命令执行请求
    public function writeRequest(array $arguments)
    {

        $commandID = strtoupper(array_shift($arguments));
        // $arguments = $arguments;
        $reqlen = count($arguments) + 1;
        $cmdlen = strlen($commandID);
        
        // 根据redis协议组合命令
        $buffer = "*{$reqlen}\r\n\${$cmdlen}\r\n{$commandID}\r\n";
        $argslen = $reqlen-1;
        if($argslen)
        {
            for($i = 0; $i < $argslen; $i++)
            {
                $arglen = strlen($arguments[$i]);
                $argument = $arguments[$i];
                $buffer .= "\${$arglen}\r\n{$argument}\r\n";
            }
        }
        
        $this->write($buffer);
    }
    
    // 执行命令
    public function write($buffer)
    {

        // $socket = $this->resource;
        $socket = $this->getResource();
        while (($length = strlen($buffer)) > 0)
        {
            $writlen = @fwrite($socket, $buffer);
            if ($length == $writlen) return;
            if ($writlen == false || $writlen == 0)
            {throw new Exception('socket write faild!');}
            
            $buffer = substr($buffer, $writlen);
        }
    }
    
    // 读取结果
    public function read()
    {

        $socket = $this->getResource();
        $chunk = fgets($socket);
        
        if ($chunk == false || $chunk == '')
        {throw new Exception('socket read faild!');}
        
        // 根据redis协议进行对返回结果处理，具体查看redis回复协议
        $prefix = $chunk[0];
        $payload = substr($chunk, 1, - 2);
        
        switch ($prefix)
        {
            case '+' : // 单行回复，回复的第一个字节将是“+”
                return $payload;
            case '-' : // 错误消息，回复的第一个字节将是“-”
                throw new Exception($payload);
                break;
            case ':': // 整型数字，回复的第一个字节将是“:”
                return (int)$payload;
            case '*': // 多个批量回复，回复的第一个字节将是“*” 
                $count = (int) $payload;
                if ($count === -1) {
                    return;
                }
                
                $multibulk = array();
                
                for ($i = 0; $i < $count; ++$i) {
                    $multibulk[$i] = $this->read(); // 使用递归方法一直获取数据
                }
                
                return $multibulk;
            case '$': // 批量回复，回复的第一个字节将是“$”
                $size = (int) $payload;
                
                if ($size === -1) {
                    return;
                }
                
                $bulkData = '';
                $bytesLeft = ($size += 2);
                
                do {
                    $chunk = fread($socket, min($bytesLeft, 4096));
                
                    if ($chunk === false || $chunk === '') {
                        throw new Exception('Error while reading bytes from the server');
                    }
                
                    $bulkData .= $chunk;
                    $bytesLeft = $size - strlen($bulkData);
                } while ($bytesLeft > 0);
                
                return substr($bulkData, 0, -2);
            default :
                throw new Exception('Unknown response prefix: '.$prefix);
                return;
        }
    }
    
    // 关闭stream socket
    public function disconnect()
    {
        if($this->isConnected())
        {
            fclose($this->resource);
            unset($this->resource);
        }
        
    }
    
    public function __destruct()
    {
        $this->disconnect();
    }

}