<?php

namespace Hydra;

class ConsumerPing implements IConsumer
{
    public function consume(MsgDTO $dto)
    {
        static $i=0;
        $i++;
        if($i%100 == 0){
            echo $i.":"."time:".time()."\n";
        }
        return true;
    }

    public function needStop($job)
    {
        return !$job?true:false;
    }
}


