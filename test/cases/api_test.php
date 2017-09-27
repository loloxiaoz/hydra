<?php

namespace Hydra;

use PHPUnit_Framework_TestCase;

//class ConsumeDemo implements Consume
//{
//    public function consume(MsgDTO $dto)
//    {
//        XLogKit::logger("tc")->debug("job: done","subs-consume") ;
//        echo "--------------------\n" ;
//        echo $dto->name  ;
//        echo " " ;
//        echo $dto->data ;
//        echo "\n" ;
//        return true ;
//
//    }
//    public function needStop($job)
//    {
//           if (!$job )  return true ;
//           return false ;
//    }
//}

class HydraTest  extends PHPUnit_Framework_TestCase
{

    public function testSubscribe()
    {
        $file = $GLOBALS["DATA_PATH"] . "/subscribe.dat" ;
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
        $file = $GLOBALS["DATA_PATH"] . "/subscribe4cmd.dat" ;
        $subs        = new Subscriber($file);
        $subs->clear();
        $commander   = new Commander($subs);
        $obj         = new Cmd() ;
        $obj->cmd    = "subscribe" ;
        $obj->client = "A" ;
        $obj->topic  = "ping" ;

        $commander->doCmd($obj);

        $obj->cmd    = "unsubscribe" ;
        $commander->doCmd($obj);
        $this->assertEquals($subs->subs("ping"), []);

    }

    public function testMain()
    {
        $consumer = new Consumer();
        $consumer->subscribe("ping","demo1",new ConsumeDemo);
        sleep(1);
        Hydra::trigger("ping","Hello") ;
        $consumer->serving($logger,1);
    }
}
