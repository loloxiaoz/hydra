<?php
require_once("init.php") ;

XSetting::logLevel("dispatch",XSetting::LOG_INFO_LEVEL) ;
$one        = new Dispatcher() ;
$confs      = HydraConfLoader::getCollectors();
$subscriber = new Subscriber();
$commonder  = new Commander($subscriber);
if(count($confs) == 1)
{

    $one->serving($confs[0],$subscriber,$commonder);
}
if(count($confs) == 2)
{
    $one->serving($confs[1],$subscriber,$commonder);
}

