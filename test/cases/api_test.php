<?php

use XCC\HydraConsume ;
use XCC\HydraDTO ;
use XCC\HydraCmd ;
use XCC\HydraSvc ;
use XCC\Hydra ;

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
        $subs->regist("ping","A") ;
        $this->assertEquals($subs->subs("ping"), ["A"]);

        $subs->regist("ping","B") ;
        $this->assertEquals($subs->subs("ping"), ["A","B"]);
        $subs->regist("ping","A") ;
        $this->assertEquals($subs->subs("ping"), ["A","B"]);

        $subs->unRegist("ping","B") ;
        $this->assertEquals($subs->subs("ping"), ["A"]);

    }
    public function testCmd()
    {
        $file = XEnv::get("RUN_PATH") . "/subscriber4cmd.dat" ;
        $subs        = new Subscriber($file);
        $subs->clear();
        $commander   = new Commander($subs);
        $obj         = new HydraCmd() ;
        $obj->cmd    = "subscribe" ;
        $obj->client = "A" ;
        $obj->topic  = "ping" ;
        $stat = new EmptyStat() ;

        $commander->doCmd($obj,$stat);

        $obj->cmd    = "unsubscribe" ;
        $commander->doCmd($obj,$stat );
        $this->assertEquals($subs->subs("ping"), []);

    }
    public function testMain()
    {

        // return ;

        $logger= XLogKit::logger("tc") ;
        $consumer = new HydraSvc();

        $consumer->subscribe("ping","demo1",new ConsumeDemo, XLogKit::logger("main"));
        sleep(1) ;
        Hydra::trigger("ping","Hello") ;
        $consumer->serving($logger,1);

    }

}
