<?php

namespace Hydra;
use Monolog\Logger;

require_once("bootstrap.php");

function createDir($dirPath)
{
    if(!file_exists($dirPath)){
        mkdir($dirPath,0777,true);
        echo '成功创建文件夹'.$dirPath."\n";
    }
}

function setupCollectorQueue()
{
    $confs      = ConfLoader::getCollectors();
    $beanstalkdPath = ConfLoader::getBeanstalkd();
    foreach($confs as $conf){
        list($ip,$port) = explode(":", $conf);
        $binlogPath     = $GLOBALS["PRJ_ROOT"]."data/hydra/beanstalk-".$port;
        system("nohup ${beanstalkdPath} -l ${ip} -p${port} -b ${binlogPath} >/dev/null 2>&1 &");
    }
}

function setupSubscriberQueue()
{
    $binlogPath = $GLOBALS["PRJ_ROOT"]."data/";
    $confs      = ConfLoader::getSubscribers();
    $beanstalkdPath = ConfLoader::getBeanstalkd();
    foreach($confs as $conf){
        list($ip,$port) = explode(":", $conf["addr"]);
        $binlogPath     = $GLOBALS["PRJ_ROOT"]."data/hydra/beanstalk-".$port;
        system("nohup ${beanstalkdPath} -l ${ip} -p${port} -b ${binlogPath} >/dev/null 2>&1 &");
    }
}

function setupDispatcher()
{
    $confs      = ConfLoader::getCollectors();
    $logger     = new MonoLogger("dispatcher",$GLOBALS["PRJ_ROOT"]."all.log",Logger::INFO);
    $dispatcher = new Dispatcher($logger) ;
    $manager    = new Manager();
    $commonder  = new Commander($manager);
    if(count($confs) == 1){
        $dispatcher->serving($confs[0], $manager, $commonder);
    }
    if(count($confs) == 2){
        $dispatcher->serving($confs[1], $manager, $commonder);
    }
}

echo "启动中\n";
setupCollectorQueue();
setupSubscriberQueue();
echo "启动成功\n";

setupDispatcher();

#$pid = pcntl_fork();
#if($pid == -1){
#    die("could not fork");
#}else if($pid == 0){
#    setupDispatcher();
#}
