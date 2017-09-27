<?php

namespace Hydra;

use Monolog\Logger;
use PHPUnit_Framework_TestCase;

class PerformanceTest extends PHPUnit_Framework_TestCase
{
    public function testMain()
    {
        $pid = pcntl_fork();
        if($pid == -1){
            die("could not fork");
        }else if($pid == 0){
            sleep(2) ;
            $count = 1000;
            for($i=0; $i<$count; $i++){
                Producer::trigger("ping",$i);
            }
        }else{
            $logger   = new MonoLogger("test", $GLOBALS["PRJ_ROOT"]."all.log", Logger::WARNING);
            $consumer = new Consumer($logger);
            $consumer->subscribe("ping", "Hping", new ConsumerPing);
            sleep(1);
            $consumer->serving(5);
            pcntl_waitpid($pid, $status);
        }
    }
}
