<?php
namespace XCC ;
require_once(dirname(dirname(__file__)) ."/xcc_sdk.php")  ;
require_once(dirname(dirname(__file__)) ."/stat/stat.php")  ;
require_once(dirname(dirname(__file__)) ."/stat/stat_impl.php")  ;
require_once(dirname(__file__) ."/hydra.php")  ;


$g_debug = false ;
if ($argc == 2 && $argv[1] == "-d" )
{
    $g_debug = true ;
}


class Ping
{
    static $recved = true ;
    static $i      = 0 ;
    static public function send()
    {
        $i = self::$i ;
        if(!self::$recved ) return false ;
        if(!self::needReceive()) return false ;
        echo "$i  Hydra ping ---- " ;
        $begin    = time();
        Hydra::trigger("ping","$i,$begin") ;
        self::$recved = false ;
        return true ;
    }
    static public function needReceive()
    {
        if (self::$i < 5 )   return true ;
        return false ;

    }
    static public function receive($dto)
    {
        list($index,$begin) = explode(',',$dto->data);
        $end   = time();
        $use   = $end - $begin;
        if((int)$index == self::$i)
        {
            self::$recved = true ;
            echo "------------------ $index use  ($use) sec\n" ;
            self::$i = self::$i+ 1 ;
            return true ;
        }
        return false ;
    }
}

class Clean implements HydraConsume
{
    public function consume(MsgDTO $dto)
    {
        echo "clean {$dto->data} \n" ;
        return true ;
    }
    public function needStop($job)
    {
        var_dump($job) ;
        // if(!$obj) return true ;
        return false ;
    }
}

class ConsumePing implements HydraConsume
{
    public function consume(MsgDTO $dto)
    {
        if( Ping::receive($dto) )
        {
            sleep(1) ;
            ping::send();
            sleep(1) ;
        }
        return true ;
    }
    public function needStop($job)
    {

        if( Ping::needReceive()) return false;
        return true ;
    }
}

class PingLogger
{
    public function __construct($debug)
    {
        $this->debug = $debug ;
    }
    public function __call($name,$params)
    {
        if($this->debug)
        {
            echo $params[0] ;
            echo "\n" ;
        }
    }
}

$logger   = new PingLogger($g_debug);
$consumer = new HydraSvc();
Hydra::$logger = $logger ;

$consumer->subscribe("ping","Hping",new ConsumePing);
sleep(1) ;
ping::send();
$consumer->serving($logger,1);
$consumer->unsubscribe("ping","Hping");
