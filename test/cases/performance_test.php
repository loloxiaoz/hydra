<?php

class ConsumePing implements HydraConsume
{
    public function consume(MsgDTO $dto)
    {
        static $i =0 ;
        $i++ ;
        if($i%100 == 0 ) echo "." ;
        return true ;
    }
    public function needStop($job)
    {
        if($job) return false  ;
        return true ;
    }
}

class PingLogger
{
    public function __call($name,$params)
    {
        // echo $params[0] ;
        // echo "\n" ;
    }
}

$pid = pcntl_fork() ;
if ($pid == -1 )
{
    die("could not fork") ;
}
else if ($pid == 0)
{
    //send process !
    sleep(2) ;
    $count = 100000 ;
    // $count = 1000 ;
    for($i =0 ; $i< $count ; $i++)
    {
        Hydra::trigger("ping" ,$i) ;
    }
}
else
{
    //main  process !
    $logger   = new PingLogger();
    $consumer = new HydraSvc();
    $consumer->subscribe("ping","Hping",new ConsumePing);
    $consumer->serving($logger,5);
    pcntl_waitpid($pid,$status) ;
    // $consumer->unsubscribe("ping","Hping");
}
