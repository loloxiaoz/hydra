<?php
namespace Hydra;

class Commander
{
    public function __construct($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function doCmd($cmd,$stat)
    {
        if($cmd->cmd == "subscribe"){
            $this->subscriber->regist($cmd->topic,$cmd->client);
        }
        if($cmd->cmd == "unsubscribe"){
            $this->subscriber->unRegist($cmd->topic,$cmd->client);
        }
    }
}
