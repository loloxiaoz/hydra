<?php

namespace Hydra;

class Dispatcher
{
    static private function subIns($topic)
    {
        static $queues = array();
        $conf = ConfLoader::getSubConf($topic);
        if(!isset($queues[$conf])){
            list($host,$port) = explode(':',$conf);
            $queues[$conf]    = new Pheanstalk_Pheanstalk($host, $port, Constants::TIMEOUT);
        }
        return $queues[$conf];
    }

    static private function getIns($conf)
    {
        list($host,$port) = explode(':', $conf);
        return new Pheanstalk_Pheanstalk($host, $port, Constants::TIMEOUT);
    }

    public function doCmd($srcQ, $subscriber, $commander, $logger, $stat)
    {
        while(true){
            $cmdJob = $srcQ->reserveFromTube(Constants::TOPIC_CMD, 0);
            if($cmdJob){
                $data = $cmdJob->getData();
                $cmd  = json_decode($data);
                $commander->doCmd($cmd,$stat);
                $logger->info("cmd: $data",  __class__);
                $srcQ->delete($cmdJob);
            }else{
                break;
            }
        }
    }

    public function doData($srcQ, $subscriber, $logger, $stat)
    {
        $count = 0;
        $begin = microtime(true);
        while(true){
            $job = $srcQ->reserveFromTube(Constants::TOPIC_EVENT, 1);
            $count++;
            if($job){
                $jId     = $job->getId();
                $logger->debug("receve job $jId", __class__);

                $data    = $job->getData();
                $dataObj = json_decode($data);
                if(empty($dataObj)){
                    $logger->warn("Job [$jId] bad format data: $data");
                    continue ;
                }
                $topic = $dataObj->name ;
                $subs  = $subscriber->subs($topic) ;
                foreach($subs as $client){
                    $dstTopic = "$topic-$client" ;
                    $logger->debug( $job->getId() . " put to $dstTopic", __class__) ;
                    $dstQ     = static::subIns($dstTopic);
                    $jobID    = $dstQ->putInTube($dstTopic, $data);
                }
                $srcQ->delete($job);
                $logger->debug("dispatched job $jId", __class__);
            }else{
                $logger->debug("no data job", __class__);
                break;
            }
            if($count > 10000 ){
                break;
            }
        }
        $end     = microtime(true);
        $usetime = sprintf("%.3f", $end - $begin);
        $logger->debug( __method__ . " use : $usetime (s)", __class__);
    }

    public function serving($src, $subscriber, $commander)
    {
        $logger = XLogKit::logger(__class__);
        $logger->info("start serving for $src", __class__);

        $srcQ = self::getIns($src);
        while(true){
            $this->doCmd($srcQ,$subscriber,$commander,$logger,$stat);
            $this->doData($srcQ,$subscriber,$logger,$stat);
        }
        $logger->info("end serving", __class__);
    }
}
