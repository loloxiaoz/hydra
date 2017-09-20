<?php

namespace Hydra;

require_once(dirname(dirname(__file__)) . "/libs/beanstalk/beanstalkmq.php");
require_once(dirname(dirname(__file__)) . "/utls/utls.php");
require_once(dirname(__file__) ."/impl/hydra_bstalk.php");

class Hydra
{
    static $logger = null;

    static public function trigger($topic, $data, $tag=null, $delay=0, $ttl=60)
    {
        static $impl  = null;
        if(empty(static::$logger)){
            static::$logger = new EmptyLogger();
        }
        $events = ConfLoader::getEvents();
        if(!in_array($topic,$events)){
            throw new \RuntimeException("Hydra not support this event: $topic");
        }
        if(empty($impl)){
            $impl = new BStalk(static::$logger);
        }
        $dto       = MsgDTO::create($topic,$data,$tag);
        $dto->encode();
        $json_data = json_encode($dto);
        $objid     = $impl->trigger(Constants::TOPIC_EVENT, $json_data, static::$logger, $delay, $ttl);
    }
}
