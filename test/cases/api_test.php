<?php

class ConsumeDemo implements HydraConsume
{
    public function consume(HydraDTO $dto)
    {
        XLogKit::logger("tc")->debug("job: done","subs-consume") ;
        echo "--------------------\n" ;
        echo $dto->name  ;
        echo " " ;
        echo $dto->data ;
        echo "\n" ;
        return true ;

    }
    public function needStop($job)
    {
           if (!$job )  return true ;
           return false ;
    }
}

class HydraTest  extends PHPUnit_Framework_TestCase
{

    public function testSubscribe()
    {
        $file = XEnv::get("RUN_PATH") . "/subscriber.dat" ;
        $subs = new Subscriber($file);
        $subs->clear();
        $subs->regist("event","A") ;
        $this->assertEquals($subs->subs("event"), ["A"]);

        $subs->regist("event","B") ;
        $this->assertEquals($subs->subs("event"), ["A","B"]);
        $subs->regist("event","A") ;
        $this->assertEquals($subs->subs("event"), ["A","B"]);

        $subs->unRegist("event","B") ;
        $this->assertEquals($subs->subs("event"), ["A"]);

    }
    public function testCmd()
    {
        $file = XEnv::get("RUN_PATH") . "/subscriber4cmd.dat" ;
        $subs        = new Subscriber($file);
        $subs->clear();
        $commander   = new Commander($subs);
        $obj         = new  HydraCmd() ;
        $obj->cmd    = "subscribe" ;
        $obj->client = "A" ;
        $obj->topic  = "event" ;

        $commander->doCmd($obj);

        $obj->cmd    = "unsubscribe" ;
        $commander->doCmd($obj);
        $this->assertEquals($subs->subs("event"), []);

    }
    public function testMain()
    {

        // return ;

        $logger= XLogKit::logger("tc") ;
        $consumer = new HydraSvc();

        $consumer->subscribe("event","demo1",new ConsumeDemo, XLogKit::logger("main"));
        sleep(1) ;
        Hydra::trigger("event","Hello") ;
        $consumer->serving($logger,1);

    }

}
