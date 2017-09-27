<?php

namespace Hydra;

use Pheanstalk\Pheanstalk;
use Pheanstalk\Exception\ConnectionException;

class BStalk
{
    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }

    public function trigger($topic, $data, $delay=0, $ttl=60)
    {
        $count = Constants::RETRY_COUNT;
        while($count>0){
            try{
                $queue = static::rollCollectIns($this->logger);
                $jobId = $queue->putInTube($topic, $data, $priority=1024, $delay, $ttl);
                $this->logger->debug("send $jobId @$topic", __method__);
                return $jobId;
            }catch(ConnectionException $e){
                $this->logger->error($e->getMessage, __method__);
            }
            $count--;
        }
    }

    public function cmd(Cmd $cmdObj)
    {
        $queues = static::collectorsIns($this->logger);
        $cmd    = get_object_vars($cmdObj);
        $bSuc   = false ;
        foreach($queues as $queue){
            try{
                $queue->putInTube(Constants::TOPIC_CMD, json_encode($cmd) );
                $bSuc = true ;
            }catch(ConnectionException $e){
                $this->logger->error($e->getMessage(),__method__) ;
            }
        }
        if(!$bSuc){
            throw new \RuntimeException("send cmd fail!") ;
        }
    }

    static private function collectorsIns($logger)
    {
        static $queues = array() ;
        if(empty($queues)){
            $confs = ConfLoader::getCollectors();
            foreach($confs as $conf){
                list($host,$port) = explode(':',$conf);
                try {
                    array_push($queues ,new Pheanstalk($host, $port, Constants::TIMEOUT));
                }catch(ConnectionException $e){
                    $logger->error($e->getMessage(), __method__) ;
                }
            }
        }
        return $queues;
    }

    static private function rollCollectIns($logger)
    {
        static $index = 0;
        $queues = static::collectorsIns($logger);
        $ins    = $queues[$index];
        $index++;
        if($index >= count($queues)){
            $index =0;
        }
        return $ins;
    }

    static private function subIns($topic,$logger)
    {
        static $queues = array();
        $conf          = ConfLoader::getSubConf($topic);
        $logger->debug("topic [$topic] use $conf");
        if(!isset($queues[$conf])){
            list($host,$port) = explode(':', $conf);
            $queues[$conf]    = new Pheanstalk($host, $port, Constants::TIMEOUT);
        }
        return $queues[$conf] ;
    }

    public function consume($topic, $workFun, $stopFun, $timeout=5)
    {
        $tag    = "consume@$topic";
        $queue  = static::subIns($topic,$this->logger);
        while(true){
            $this->logger->info("watch $topic", "consume");
            $job    = $queue->watch($topic)->ignore('default')->reserve($timeout);
            $result = false;
            if(is_callable($stopFun) && call_user_func($stopFun,$job) == true ){
                return;
            }
            if(!$job){
                $this->logger->debug("no job ", $tag);
                continue;
            }
            $jId  = $job->getId();
            $data = $job->getData();
            $this->logger->debug("get job: $jId", $tag);
            try{
                $result = call_user_func($workFun, $data);
            }catch(Exception $e){
                $this->logger->warn("job failed: " . $e->getMessage(), $tag);
            }
            if($result === true){
                $this->logger->info("job[$jId] proc suc!", $tag);
                $queue->delete($job);
            }else{
                $queue->release($job, 1024, Constants::RETRY_TIME);
            }
        }
    }
}
