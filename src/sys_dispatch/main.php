<?php
require_once("init.php") ;
use XCC\HydraConfLoader ;
XCC\XSdkEnv::init();
XSetting::logLevel("dispatch",XSetting::LOG_INFO_LEVEL) ;
HydraSetting::set_stat(new SentryStat()) ;
$one        = new Dispatcher() ;
$confs      = HydraConfLoader::getCollectors();
$subscriber = new Subscriber();
$commonder  = new Commander($subscriber);
$one->serving($confs[0],$subscriber,$commonder);

