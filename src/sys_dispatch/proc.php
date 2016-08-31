<?php
require_once("init.php") ;

class ConsumeDemo implements HydraConsume
{
    public function consume(HydraDTO $dto) 
    {
        XLogKit::logger("tc")->debug("job: done","subs-consume") ;
        return true ;

    }
    public function needStop($job)
    {
           // if (!$job )  return true ;
           return false ;
    }
}

$logger= XLogKit::logger("perforce") ;
Hydra::subscribe("event","demo1");
sleep(1) ;
Hydra::consume("demo1","event",new ConsumeDemo,$logger,5);
