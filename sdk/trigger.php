<?php

namespace Hydra;

require_once(dirname(dirname(__file__)) . "/libs/beanstalk/beanstalkmq.php");
require_once(dirname(dirname(__file__)) . "/utls/utls.php");
require_once(dirname(__file__) ."/impl/hydra_bstalk.php");

class MsgDTO
{
    public $name;
    public $data;
    public $cls;
    public $tag;
    public $delay;
    public $happen;

    static public function create($name, $data, $tag, $delay)
    {
        $cls         = __class__;
        $dto         = new $cls;
        $dto->happen = time();
        $dto->name   = $name;
        $dto->tag    = $tag;
        if(is_object($data)){
            $dto->cls  = get_class($data);
        }
        $dto->delay = $delay;
        $dto->data  = $data;
        return $dto;
    }

    static public function fromJson($json)
    {
        $cls          = __class__;
        $dto          = new $cls;
        $obj          = json_decode($json);
        if($obj == null){
            return null;
        }
        $dto->name    = $obj->name;
        $dto->happen  = $obj->happen;
        $dto->tag     = $obj->tag;
        $dto->cls     = $obj->cls;
        $dto->delay   = $obj->delay;
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

class Hydra
{
    static $logger;
    static $impl;

    static private function check($topic)
    {
        if(empty(static::$logger)){
            static::$logger = new EmptyLogger();
        }
        if(empty(static::$impl)){
            static::$impl = new BStalk(static::$logger);
        }
        $events = ConfLoader::getEvents();
        if(!in_array($topic, $events)){
            throw new \RuntimeException("not support this event: $topic");
        }
    }

    static public function trigger($topic, $data, $tag=null, $delay=0, $ttl=60)
    {
        static::check($topic);
        $dto       = MsgDTO::create($topic, $data, $tag, $delay);
        $dto->encode();
        $json_data = json_encode($dto);
        return $impl->trigger(Constants::TOPIC_EVENT, $json_data, $delay, $ttl);
    }
}
