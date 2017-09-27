<?php

namespace Hydra;

class ConsumerDemo implements IConsumer
{
    public function consume(MsgDTO $dto)
    {
        echo "--------------------\n" ;
        echo $dto->name  ;
        echo " " ;
        echo $dto->data ;
        echo "\n" ;
        return true ;

    }
    public function needStop($job)
    {
        return !$job?true:false;
    }
}
