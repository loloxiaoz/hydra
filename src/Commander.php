<?php

namespace Hydra;

class Commander
{
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function doCmd($cmd)
    {
        if($cmd->cmd == "subscribe"){
            $this->manager->regist($cmd->topic,$cmd->client);
        }
        if($cmd->cmd == "unsubscribe"){
            $this->manager->unRegist($cmd->topic,$cmd->client);
        }
    }
}
