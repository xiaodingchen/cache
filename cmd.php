<?php

class cmd {

    public function __construct()
    {

        set_time_limit(0);
        $timezone = 8;
        date_default_timezone_set('Etc/GMT' . ($timezone >= 0 ? ($timezone * - 1) : '+' . ($timezone * - 1)));
    }

    public function run()
    {

        ignore_user_abort(false);
        ob_implicit_flush(1);
        ini_set('implicit_flush', true);
        if (strpos(strtolower(PHP_OS), 'win') === 0)
        {
            if (function_exists('mb_internal_encoding'))
            {
                mb_internal_encoding("UTF-8");
                mb_http_output("GBK");
                ob_start("mb_output_handler", 2);
            }
            elseif (function_exists('iconv_set_encoding'))
            {
                iconv_set_encoding("internal_encoding", "UTF-8");
                iconv_set_encoding("output_encoding", "GBK");
                ob_start("ob_iconv_handler", 2);
            }
        }
        
        if (isset($_SERVER ['argv'] [1]))
        {
            array_shift($_SERVER['argv']);
            echo implode(' ', $_SERVER['argv'])." ok.\n";
        }
        else
        {
            $this->interactive();
        }
        // var_dump($_SERVER['argv']);
    }
    
    //
    public function print_welcome()
    {

        echo 'this is php shell. Now,you can input somting args';
    }

   

    // 客户端交互
    public function interactive()
    {

        $this->print_welcome();
        $i = 1;
        while (true)
        {
            // code...
            $line = readline("\n" . $i ++ . '>');
            readline_add_history($line);
            $this->exec($line);
        }
    }
    
    // 执行命令
    public function exec($command)
    {
        $command = trim($command);
        if ($command == '')
        {
            echo "please input command";
        }else{
            // 如果输入的命令以;号结尾，那就作为php执行
            if(substr($command, -1, 1) == ';')
            {
                $this->phpCall($command);
            }else{
                // 执行自定义的命令
                echo $command;
            }
        }
    
    }
    // 处理简单的php语句
    public function phpCall()
    {
        $this->output(eval(func_get_arg(0)));
    }
    
    // 处理输出
    public function output()
    {
        $args = func_get_args();
        foreach($args as $data){
            switch(gettype($data)){
                case 'object':
                    echo 'Object<'.get_class($data).">\n";
                    break;
        
                case 'integer':
                case 'double':
                case 'resource':
                case 'string':
                    echo $data;
                    break;
        
                case 'array':
                    print_r($data);
        
                default:
                    var_dump($data);
            }
        }
    }

}