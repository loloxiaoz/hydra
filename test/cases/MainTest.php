<?php

namespace Hydra;

use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class MainTest  extends PHPUnit_Framework_TestCase
{
    public function testSubscribe()
    {
        $file = $GLOBALS["DATA_PATH"] . "/subscribe.dat" ;
        $subs = new Manager($file);
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
        $subs        = new Manager($file);
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
        $logger     = new MonoLogger("test",$GLOBALS["PRJ_ROOT"]."all.log",Logger::INFO);
        $consumer   = new Consumer($logger);
        $consumer->subscribe("ping","demo1",new ConsumerDemo);
        sleep(1);
        Producer::trigger("ping","Hello") ;
        $consumer->serving(1);
    }
}
