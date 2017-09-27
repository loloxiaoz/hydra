<?php

namespace Hydra;

use Monolog\Logger;

class Producer
{
    static $logger;
    static $impl;

    static private function check($topic)
    {
        if(empty(static::$impl)){
            static::$logger = new MonoLogger("producer",$GLOBALS["PRJ_ROOT"]."all.log",Logger::WARNING);
            static::$impl   = new BStalk(static::$logger);
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
        return static::$impl->trigger(Constants::TOPIC_EVENT, $json_data, $delay, $ttl);
    }
}
