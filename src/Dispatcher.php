<?php

namespace Hydra;

use Pheanstalk\Pheanstalk;

class Dispatcher
{
    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }

    static private function subIns($topic)
    {
        static $queues = array();
        $conf = ConfLoader::getSubConf($topic);
        if(!isset($queues[$conf])){
            list($host,$port) = explode(':',$conf);
            $queues[$conf]    = new Pheanstalk($host, $port, Constants::TIMEOUT);
        }
        return $queues[$conf];
    }

    static private function getIns($conf)
    {
        list($host,$port) = explode(':', $conf);
        return new Pheanstalk($host, $port, Constants::TIMEOUT);
    }

    public function doCmd($srcQ, Commander $commander)
    {
        while(true){
            $cmdJob = $srcQ->reserveFromTube(Constants::TOPIC_CMD, 0);
            if(!$cmdJob){
                break;
            }
            $data = $cmdJob->getData();
            $cmd  = json_decode($data);
            $commander->doCmd($cmd);
            $this->logger->info("cmd: $data", __class__);
            $srcQ->delete($cmdJob);
        }
    }

    public function doEvent($srcQ, Manager $manager)
    {
        $count = 0;
        $begin = microtime(true);
        while(true){
            $job = $srcQ->reserveFromTube(Constants::TOPIC_EVENT, 1);
            $count++;
            if($job){
                $jobId     = $job->getId();
                $this->logger->debug("receve job $jobId", __class__);

                $data    = $job->getData();
                $dataObj = json_decode($data);
                if(empty($dataObj)){
                    $this->logger->warn("Job [$jobId] bad format data: $data", __class__);
                    continue ;
                }
                $topic = $dataObj->name ;
                $subs  = $manager->subs($topic) ;
                foreach($subs as $client){
                    $dstTopic = "$topic-$client" ;
                    $this->logger->debug($job->getId() . " put to $dstTopic", __class__) ;
                    $dstQ     = static::subIns($dstTopic);
                    $jobID    = $dstQ->putInTube($dstTopic, $data);
                }
                $srcQ->delete($job);
                $this->logger->debug("dispatched job $jobId", __class__);
            }else{
                $this->logger->debug("no data job", __class__);
                break;
            }
            if($count > 10000){
                break;
            }
        }
        $end     = microtime(true);
        $usetime = sprintf("%.3f", $end - $begin);
        $this->logger->debug( __method__ . " use : $usetime (s)", __class__);
    }

    public function serving($conf, Manager $manager, Commander $commander)
    {
        $this->logger->info("start serving for $src", __class__);
        $srcQ = static::getIns($conf);
        while(true){
            $this->doCmd($srcQ, $commander);
            $this->doEvent($srcQ, $manager);
        }
        $this->logger->info("end serving", __class__);
    }
}
