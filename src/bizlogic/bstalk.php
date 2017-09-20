<?php

namespace Hydra;

class BStalk implements ICollector, IConsumer
{
    private $logger;

    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }

    public function trigger($topic, $data, $delay=0, $ttl=60)
    {
        $count = Constants::RETRY_COUNT;
        while($count>0){
            try{
                $Q     = static::rollCollectIns();
                $jobId = $Q->putInTube($topic, $data, $priority=1024, $delay, $ttl);
                $this->logger->debug("send $jobId @$topic");
                return $jobId;
            }catch(Pheanstalk_Exception_ConnectionException $e){
                $this->logger->error($e->getMessage,__method__);
            }
            $count--;
        }
    }

    public function cmd(Cmd $cmdObj)
    {
        $queues = static::collectorsIns();
        $cmd    = get_object_vars($cmdObj);
        $bSuc   = false ;
        foreach($queues as $Q){
            try{
                $Q->putInTube(Constants::TOPIC_CMD, json_encode($cmd) );
                $bSuc = true ;
            }catch(Pheanstalk_Exception_ConnectionException $e){
                $this->logger->error($e->getMessage(),__method__) ;
            }
        }
        if(!$bSuc){
            throw new \RuntimeException("send cmd fail!") ;
        }
    }

    static private function collectorsIns()
    {
        static $queues = array() ;
        if(empty($queues)){
            $confs = ConfLoader::getCollectors();
            foreach($confs as $conf){
                list($host,$port) = explode(':',$conf);
                try {
                    array_push($queues ,new \Pheanstalk_Pheanstalk($host, $port, Constants::TIMEOUT));
                }catch(\Pheanstalk_Exception_ConnectionException $e){
                    $this->logger->error($e->getMessage(),__method__) ;
                }
            }
        }
        return $queues;
    }

    static private function rollCollectIns()
    {
        static $index = 0;
        $queues = static::collectorsIns();
        $ins    = $queues[$index];
        $index++;
        if($index >= count($queues)){
            $index =0;
        }
        return $ins;
    }

    static private function subIns($topic)
    {
        static $queues = array();
        $conf          = ConfLoader::getSubConf($topic);
        $this->logger->debug("topic [$topic] use $conf");
        if(!isset($queues[$conf])){
            list($host,$port) = explode(':', $conf);
            $queues[$conf]    = new \Pheanstalk_Pheanstalk($host, $port, Constants::TIMEOUT);
        }
        return $queues[$conf] ;
    }

    public function consume($topic, $workFun, $stopFun, $timeout=5)
    {
        $tag   = "consume@$topic";
        $Q     = static::subIns($topic);
        while(true){
            $this->logger->info("watch $topic", "consume");
            $job    = $Q->watch($topic)->ignore('default')->reserve($timeout);
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
                $Q->delete($job);
            }else{
                $Q->release($job, 1024, Constants::RETRY_TIME);
            }
        }
    }
}
