<?php
namespace Hydra;

class Constants
{
    const TOPIC_CMD     = "__CMD__";
    const TOPIC_EVENT   = "__EVENT__";

    const TIMEOUT       = 500;  //超时时间
    const RETRY_TIME    = 10;   //重试时间
    const RETRY_COUNT   = 2;    //重试次数
}

class Cmd
{
    public $cmd;
    public $topic;
    public $client;
}

interface ICollector
{
    public function trigger($topic, $data, $logger, $delay=0, $ttl=60);
}

interface IConsumer
{
    public function cmd(Cmd $cmd, $logger);
    public function consume($topic, $workFun, $stopFun, $logger, $timeout=5);
}

class MsgDTO
{
    public $name;
    public $data;
    public $cls;
    public $tag;
    public $happen;

    static public function create($name, $data, $tag)
    {
        $cls         = __class__;
        $dto         = new $cls;
        $dto->happen = time();
        $dto->name   = $name;
        $dto->tag    = $tag;
        if(is_object($data)){
            $dto->cls  = get_class($data);
        }
        $dto->data = $data;
        return $dto;
    }

    static public function fromJson($json)
    {
        $cls          = __class__;
        $dto          = new $cls;
        $obj          = json_decode($json);
        if($obj  == null ) return null;
        $dto->name    = $obj->name;
        $dto->happen  = $obj->happen;
        $dto->tag     = $obj->tag;
        $dto->cls     = $obj->cls;
        $dto->data    = $obj->data;
        return $dto;
    }

    public function encode()
    {
        $data = $this->data;
        if(is_object($data)){
            $data = json_encode($data);
        }
        $this->data = base64_encode($data);
    }

    public function decode()
    {
        $odata = base64_decode($this->data);
        if($this->cls != null){
            $data = json_decode($odata);
        }
        $this->data = $data==null? $odata : $data;
    }
}
