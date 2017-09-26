<?php
require_once("init.php") ;

$one        = new Dispatcher() ;
$confs      = ConfLoader::getCollectors();
$subscriber = new Subscriber();
$commonder  = new Commander($subscriber);
if(count($confs) == 1){
    $one->serving($confs[0], $subscriber, $commonder);
}
if(count($confs) == 2){
    $one->serving($confs[1], $subscriber, $commonder);
}
