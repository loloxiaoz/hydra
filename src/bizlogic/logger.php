<?php

interface ILogger
{
    public function error($msg, $tag);
    public function info($msg, $tag);
    public function debug($msg, $tag);
}

class EchoLogger implements ILogger
{
    public function error($msg, $tag="")
    {
        echo "error: $msg\n";
    }

    public function info($msg, $tag="")
    {
        echo "info: $msg\n";
    }
    public function debug($msg, $tag="")
    {
        echo "debug: $msg\n";
    }
}
