<?php

//@REST_RULE: /stats/$method,/stats/$method/$item
class StateSvc extends XRuleService implements XService
{

    public function queues($xcontext,$request,$response)
    {
        $collectorConfs = HydraConfLoader::getCollectors();
        $subscribes     = HydraConfLoader::getSubscibes();
        $statsData      = array();

        foreach($collectorConfs as $conf )
        {
            list($host,$port) = explode(':',$conf) ;
            $one  = new Pheanstalk_Pheanstalk($host, $port, HydraDefine::TIMEOUT) ;
            $data = $one->stats();
            $statsData[$conf] = $data ;
        }

        foreach($subscribes as $conf )
        {
            list($host,$port) = explode(':',$conf) ;
            $one  = new Pheanstalk_Pheanstalk($host, $port, HydraDefine::TIMEOUT) ;
            $data = $one->stats();
            $statsData[$conf] = $data ;
        }
        $response->success($statsData);
    }

    public function subs($xcontext,$request,$response)
    {
        $collectorConfs = HydraConfLoader::getCollectors();
        $subscribes     = HydraConfLoader::getSubscibes();
        $statsData      = array();
        $item  = $request->item ;


        if(empty($item))
        {
            foreach($subscribes as $conf )
            {
                list($host,$port) = explode(':',$conf) ;
                $one              = new Pheanstalk_Pheanstalk($host, $port, HydraDefine::TIMEOUT) ;
                $data             = $one->listTubes();
                $statsData[$conf] = $data ;
            }
        }
        else
        {
            $conf             = HydraConfLoader::getSubConf($item) ;
            list($host,$port) = explode(':',$conf) ;
            $one              = new Pheanstalk_Pheanstalk($host, $port, HydraDefine::TIMEOUT) ;
            $data             = $one->statsTube($item);
            $statsData[$conf] = $data ;

        }
        $response->success($statsData);
    }
    public function events($xcontext,$request,$response)
    {

        $collectorConfs = HydraConfLoader::getCollectors();
        $subscribes     = HydraConfLoader::getSubscibes();
        $statsData      = array();

        foreach($collectorConfs as $conf )
        {
            list($host,$port) = explode(':',$conf) ;
            $one  = new Pheanstalk_Pheanstalk($host, $port, HydraDefine::TIMEOUT) ;
            $data = $one->statsTube(HydraDefine::TOPIC_EVENT);
            $statsData[$conf] = $data ;
        }
        $response->success($statsData);
    }

    // public function cmds($xcontext,$request,$response)
    // {
    //
    //     $collectorConfs = HydraConf::$queues ;
    //     $statsData      = array();
    //
    //     foreach($collectorConfs as $conf )
    //     {
    //         list($host,$port) = explode(':',$conf) ;
    //         $one  = new Pheanstalk_Pheanstalk($host, $port, HydraDefine::TIMEOUT) ;
    //         // $data = $one->statsTube(HydraConf::TOPIC_CMD);
    //         // $data = $one->statsTube("__CMD__");
    //         $data = $one->listTubes();
    //         $statsData[$conf] = $data ;
    //     }
    //     $response->success($statsData);
    // }
}
