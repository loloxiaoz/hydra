<?php

namespace Hydra;

class Commander
{
    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function doCmd(Cmd $cmd)
    {
        if($cmd->cmd == "subscribe"){
            $this->subscriber->regist($cmd->topic,$cmd->client);
        }
        if($cmd->cmd == "unsubscribe"){
            $this->subscriber->unRegist($cmd->topic,$cmd->client);
        }
    }
}
