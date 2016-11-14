<?php
use XCC\HydraDefine ;
use XCC\HydraConfLoader ;
class Dispatcher
{
    public function __construct()
    {

    }
    static private function subIns($topic)
    {
        static $queues = array() ;
        $conf = HydraConfLoader::getSubConf($topic);
        if(!isset($queues[$conf]))
        {
            list($host,$port) = explode(':',$conf) ;
            $queues[$conf]    = new Pheanstalk_Pheanstalk($host, $port, HydraDefine::TIMEOUT);
        }
        return $queues[$conf] ;

    }
    static private function getIns($conf)
    {
        list($host,$port) = explode(':',$conf) ;
        $ins = new Pheanstalk_Pheanstalk($host, $port, HydraDefine::TIMEOUT);
        return $ins ;
    }
    public function doCmd($srcQ,$subscriber,$commander,$logger,$stat)
    {
        while(true)
        {
            $cmdJob = $srcQ->reserveFromTube(HydraDefine::TOPIC_CMD,0) ;
            if($cmdJob)
            {
                $data = $cmdJob->getData() ;
                $cmd  = json_decode($data) ;
                $commander->doCmd($cmd,$stat);
                $logger->info("cmd: $data","dispatch") ;
                $srcQ->delete($cmdJob) ;
            }
            else
            {
                // $logger->debug("no cmd job","dispatch") ;
                break;
            }
        }

    }

    public function doData($srcQ,$subscriber,$logger,$stat)
    {
        $count = 0 ;
        $begin = microtime(true);
        while(true)
        {
            $job = $srcQ->reserveFromTube(HydraDefine::TOPIC_EVENT,1) ;
            $count++ ;
            if($job)
            {
                $jid     = $job->getId();
                $logger->debug("receve job $jid","dispatch") ;

                $data    = $job->getData();
                $dataObj = json_decode($data) ;
                if($dataObj == null)
                {
                    $logger->warn("Job [$jid] bad format data: $data") ;
                    continue ;
                }
                $topic = $dataObj->name ;
                $stat->stat($topic);
                $subs  = $subscriber->subs($topic) ;
                foreach($subs as $client)
                {
                    $dstTopic = "$topic-$client" ;
                    $logger->debug( $job->getId() . " put to $dstTopic","dispatch") ;
                    $dstQ     = self::subIns($dstTopic) ;
                    $jobID    = $dstQ->putInTube($dstTopic, $data);
                }
                $srcQ->delete($job);
                $logger->debug("dispatched job $jid","dispatch") ;
            }
            else
            {
                $logger->debug("no data job","dispatch") ;
                break;
            }
            //do some times break;
            if($count > 10000 ) break;
        }
        $end     = microtime(true);
        $usetime = sprintf("%.3f", $end -$begin);
        $logger->debug( __method__ . " use : $usetime (s)","dispatch") ;

    }

    public function serving($src,$subscriber,$commander)
    {
        $logger = XLogKit::logger("dispatch")  ;
        $logger->info("start serving for $src","dispatch") ;

        $srcQ  = self::getIns($src) ;
        $stat  = HydraSetting::get_stat();
        while(true)
        {
            $this->doCmd($srcQ,$subscriber,$commander,$logger,$stat) ;
            $this->doData($srcQ,$subscriber,$logger,$stat) ;

        }
        $logger->info("end serving ","dispatch") ;

    }
}
