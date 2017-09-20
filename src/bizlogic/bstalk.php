<?php

namespace Hydra;

class BStalk implements ICollector, IConsumer
{
    public function trigger($topic, $data, $logger, $delay=0, $ttl=60)
    {
        for($i=0; $i<2; $i++){
            try{
                $Q     = self::rollIns();
                $jobId = $Q->putInTube($topic, $data, $priority=1024, $delay, $ttl);
                $logger->debug("send $jobId @$topic");
                return $jobId;
            }catch(Pheanstalk_Exception_ConnectionException $e){
                $logger->error($e->getMessage) ;
            }
        }
    }

    public function cmd(HydraCmd $cmdObj, $logger)
    {
        $queues = self::collectorsIns();
        $cmd    = get_object_vars($cmdObj);
        $bSuc   = false ;
        foreach($queues as $Q){
            try{
                $Q->putInTube(Constants::TOPIC_CMD, json_encode($cmd) );
                $bSuc = true ;
            }catch(Pheanstalk_Exception_ConnectionException $e){
                $logger->error($e->getMessage()) ;
            }
        }
        if(!$bSuc){
            throw new RuntimeException("send hydra cmd fail!") ;
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
                    echo $e->getMessage();
                }
            }
        }
        return $queues;
    }

    static private function rollIns()
    {
        static $index = 0;
        $queues = self::collectorsIns();
        $ins    = $queues[$index];
        $index++;
        if($index >= count($queues)){
            $index =0;
        }
        return $ins;
    }

    static private function subIns($topic, $logger)
    {
        static $queues = array();
        $conf          = ConfLoader::getSubConf($topic);
        $logger->debug("topic [$topic] use $conf");
        if(!isset($queues[$conf])){
            list($host,$port) = explode(':', $conf);
            $queues[$conf]    = new \Pheanstalk_Pheanstalk($host, $port, Constants::TIMEOUT);
        }
        return $queues[$conf] ;
    }

    public function consume($topic, $workFun, $stopFun, $logger, $timeout=5)
    {
        $tag   = "consume@$topic";
        $Q     = self::subIns($topic, $logger);
        while(true){
            $logger->info("watch $topic", "consume");
            $job    = $Q->watch($topic)->ignore('default')->reserve($timeout);
            $result = false;
            if(is_callable($stopFun) && call_user_func($stopFun,$job) == true ){
                return;
            }
            if(!$job){
                $logger->debug("no job ", $tag);
                continue;
            }
            $jId  = $job->getId();
            $data = $job->getData();
            $logger->debug("get job: $jId", $tag);
            try{
                $result = call_user_func($workFun, $data);
            }catch(Exception $e){
                $logger->warn("job failed: " . $e->getMessage(), $tag);
            }
            if($result === true){
                $logger->info("job[$jId] proc suc!", $tag);
                $Q->delete($job);
            }else{
                $Q->release($job, 1024, Constants::RETRY_TIME);
            }
        }
    }
}
