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
    const TIME_OUT = 5.0;

    public function __construct($remote)
    {

        $this->remote = $remote;
        $this->connect();
    }

    public function connect()
    {

        $scoket = stream_socket_client($this->remote, $errno, $errstr, (float) self::TIME_OUT, STREAM_CLIENT_CONNECT);
        stream_set_timeout($scoket, 100);
        $this->resource = $scoket;
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

        $socket = $this->resource;
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

        $socket = $this->resource;
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

}