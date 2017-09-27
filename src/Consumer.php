<?php

namespace Hydra;

class Consumer
{
    private $consumeTopic =  null ;
    private $consumeObj   =  null ;

    public function __construct(ILogger $logger)
    {
        $this->impl = new BStalk($logger);
    }

    public function serving($timeout = 5)
    {
        $topic      = $this->consumeTopic;
        $consumeObj = $this->consumeObj;
        $tag        = "consume:@$topic";
        $call       = function ($data)use($consumeObj,$tag){
            $host       = gethostname();
            $this->$logger->debug("job data:" . $data, $tag);
            $obj        = MsgDTO::fromJson($data);
            if(empty($obj)){
                $this->logger->warn("bad MsgDTO",$tag);
                return;
            }
            $obj->decode();
            return $consumeObj->consume($obj);
        };
        return $this->impl->consume($topic, $call, array($consumeObj,"needStop"), $timeout);
    }

    public function subscribe($topic, $client, $consumeObj)
    {
        $cmd                = new Cmd;
        $cmd->cmd           = "subscribe";
        $cmd->client        = $client;
        $cmd->topic         = $topic;
        $this->impl->cmd($cmd);
        $this->consumeTopic = "$topic-$client";
        $this->consumeObj   = $consumeObj;
    }

    public function unSubscribe($topic, $client)
    {
        $cmd                = new Cmd;
        $cmd->cmd           = "unsubscribe";
        $cmd->client        = $client;
        $cmd->topic         = $topic;
        $this->impl->cmd($cmd);
        $this->consumeTopic = null;
        $this->consumeObj   = null;
    }
}
