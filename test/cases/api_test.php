<?php

namespace Hydra;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit_Framework_TestCase;

class ConsumerDemo implements IConsumer
{
   public function consume(MsgDTO $dto)
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
        $logger     = new Logger('name');
        $logger->pushHandler(new StreamHandler($GLOBALS["PRJ_ROOT"], Logger::WARNING));
        $consumer   = new Consumer($logger);
        $consumer->subscribe("ping","demo1",new ConsumerDemo);
        sleep(1);
        Client::trigger("ping","Hello") ;
        $consumer->serving($logger,1);
    }
}
